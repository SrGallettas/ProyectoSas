<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Business;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        /** @var Business $activeBusiness */
        $activeBusiness = $request->attributes->get('activeBusiness');
        $sales = $this->filteredSalesQuery($request, $activeBusiness)
            ->with(['customer', 'user'])
            ->latest('sold_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();
        $customers = $activeBusiness->customers()->orderBy('name')->get();

        return view('sales.index', compact('activeBusiness', 'customers', 'sales'));
    }

    public function export(Request $request): StreamedResponse
    {
        /** @var Business $activeBusiness */
        $activeBusiness = $request->attributes->get('activeBusiness');
        $sales = $this->filteredSalesQuery($request, $activeBusiness)
            ->with(['customer', 'user'])
            ->latest('sold_at')
            ->latest('id')
            ->get();

        return response()->streamDownload(function () use ($sales): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Ticket', 'Fecha', 'Empleado', 'Cliente', 'Método de pago', 'Total'], ';');

            foreach ($sales as $sale) {
                fputcsv($output, [
                    $sale->id,
                    $sale->sold_at->format('d/m/Y H:i'),
                    $sale->user?->name ?? 'Sin registrar',
                    $sale->customer?->name ?? 'Sin identificar',
                    $sale->paymentMethodLabel(),
                    number_format((float) $sale->total, 2, ',', ''),
                ], ';');
            }

            fclose($output);
        }, 'ventas-'.$activeBusiness->id.'-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        /** @var Business $activeBusiness */
        $activeBusiness = $request->attributes->get('activeBusiness');
        $categories = $activeBusiness->categories()->with(['products' => fn ($query) => $query->orderBy('name')])->orderBy('name')->get();
        $uncategorizedProducts = $activeBusiness->products()->whereNull('category_id')->orderBy('name')->get();
        $customers = $activeBusiness->customers()->orderBy('name')->get();
        $checkoutToken = old('checkout_token', (string) Str::uuid());

        return view('sales.create', compact('activeBusiness', 'categories', 'customers', 'uncategorizedProducts', 'checkoutToken'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSaleRequest $request): RedirectResponse
    {
        /** @var Business $activeBusiness */
        $activeBusiness = $request->attributes->get('activeBusiness');
        $quantities = collect($request->validated('products'))->map(fn ($quantity): int => (int) $quantity)->filter(fn (int $quantity): bool => $quantity > 0);
        if ($quantities->isEmpty()) {
            throw ValidationException::withMessages(['products' => 'Añade al menos un producto.']);
        }
        $customerId = $request->validated('customer_id');
        $paymentMethod = $request->validated('payment_method');
        $checkoutToken = $request->validated('checkout_token');
        $userId = $request->user()->id;
        if ($customerId !== null && ! $activeBusiness->customers()->whereKey($customerId)->exists()) {
            throw ValidationException::withMessages(['customer_id' => 'El cliente no pertenece al comercio activo.']);
        }

        try {
            $sale = DB::transaction(function () use ($activeBusiness, $customerId, $paymentMethod, $checkoutToken, $quantities, $userId): Sale {
                $products = $activeBusiness->products()->whereKey($quantities->keys())->lockForUpdate()->get()->keyBy('id');
                if ($products->count() !== $quantities->count()) {
                    throw ValidationException::withMessages(['products' => 'Uno de los productos no pertenece al comercio activo.']);
                }
                $totalCents = 0;
                foreach ($quantities as $productId => $quantity) {
                    $product = $products->get($productId);
                    if ($product->stock !== null && $product->stock < $quantity) {
                        throw ValidationException::withMessages(["products.$productId" => "No hay stock suficiente de {$product->name}."]);
                    }
                    $totalCents += $this->priceToCents($product->price) * $quantity;
                }
                $sale = $activeBusiness->sales()->create([
                    'user_id' => $userId,
                    'customer_id' => $customerId,
                    'total' => $this->centsToPrice($totalCents),
                    'payment_method' => $paymentMethod,
                    'checkout_token' => $checkoutToken,
                    'sold_at' => now(),
                ]);
                foreach ($quantities as $productId => $quantity) {
                    /** @var Product $product */
                    $product = $products->get($productId);
                    $lineCents = $this->priceToCents($product->price) * $quantity;
                    $sale->lines()->create(['product_id' => $product->id, 'product_name' => $product->name, 'quantity' => $quantity, 'unit_price' => $product->price, 'line_total' => $this->centsToPrice($lineCents)]);
                    if ($product->stock !== null) {
                        $product->decrement('stock', $quantity);
                    }
                }

                return $sale;
            });
        } catch (UniqueConstraintViolationException $exception) {
            $sale = $activeBusiness->sales()->where('checkout_token', $checkoutToken)->first();

            if ($sale === null) {
                throw $exception;
            }
        }

        return redirect()->route('sales.show', $sale)->with('status', 'Venta registrada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Sale $sale): View
    {
        /** @var Business $activeBusiness */
        $activeBusiness = $request->attributes->get('activeBusiness');
        $sale->load(['customer', 'lines', 'user']);

        return view('sales.show', compact('activeBusiness', 'sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    private function priceToCents(string $price): int
    {
        [$whole, $decimal] = array_pad(explode('.', $price, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($decimal, 0, 2), 2, '0');
    }

    private function centsToPrice(int $cents): string
    {
        return sprintf('%d.%02d', intdiv($cents, 100), $cents % 100);
    }

    /** @return Builder<Sale> */
    private function filteredSalesQuery(Request $request, Business $activeBusiness): Builder
    {
        $query = $activeBusiness->sales()->getQuery();
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();
        $paymentMethod = $request->string('payment_method')->toString();
        $customerId = $request->integer('customer_id');
        $ticketId = $request->integer('ticket_id');

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) === 1) {
            $query->whereDate('sold_at', '>=', $dateFrom);
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo) === 1) {
            $query->whereDate('sold_at', '<=', $dateTo);
        }
        if ($customerId > 0) {
            $query->where('customer_id', $customerId);
        }
        if (in_array($paymentMethod, [Sale::PAYMENT_CASH, Sale::PAYMENT_CARD], true)) {
            $query->where('payment_method', $paymentMethod);
        }
        if ($ticketId > 0) {
            $query->whereKey($ticketId);
        }

        return $query;
    }
}
