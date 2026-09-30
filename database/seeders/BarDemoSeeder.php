<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BarDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $user = User::query()->updateOrCreate(['email' => 'demo@cafeteria.test'], ['name' => 'Dueño Cafetería Demo', 'password' => Hash::make('password'), 'email_verified_at' => now()]);
            $business = Business::query()->updateOrCreate(['user_id' => $user->id, 'name' => 'Café Alameda']);
            $catalogue = [
                'Cafés' => [['Café solo', 1.40], ['Café cortado', 1.60], ['Café con leche', 1.80], ['Café americano', 1.70], ['Capuchino', 2.40], ['Café bombón', 2.30], ['Descafeinado', 1.60], ['Café con hielo', 1.60]],
                'Tés e infusiones' => [['Té negro', 1.80], ['Té verde', 1.80], ['Manzanilla', 1.70], ['Menta poleo', 1.70], ['Rooibos', 2.00]],
                'Bebidas frías' => [['Agua 50 cl', 1.50], ['Agua con gas', 1.80], ['Refresco', 2.20], ['Zumo de naranja natural', 3.20], ['Batido de chocolate', 2.50], ['Tónica', 2.30]],
                'Cervezas y vinos' => [['Caña', 1.80], ['Doble de cerveza', 2.80], ['Cerveza sin alcohol', 2.40], ['Copa de vino tinto', 2.80], ['Copa de vino blanco', 2.80], ['Vermut', 3.20]],
                'Desayunos' => [['Tostada con tomate', 2.50], ['Tostada con mantequilla', 2.20], ['Tostada con jamón', 3.80], ['Croissant', 2.00], ['Napolitana de chocolate', 2.20], ['Churros', 2.50]],
                'Tapas y raciones' => [['Tortilla española', 3.50], ['Ensaladilla rusa', 4.50], ['Croquetas', 6.50], ['Patatas bravas', 5.50], ['Calamares', 8.50], ['Tabla de queso', 9.00], ['Aceitunas', 2.00]],
                'Bocadillos' => [['Bocadillo de tortilla', 5.00], ['Bocadillo de jamón', 5.50], ['Bocadillo de calamares', 6.50], ['Sándwich mixto', 4.50]],
                'Postres' => [['Tarta de queso', 4.50], ['Flan casero', 3.50], ['Brownie', 4.00], ['Helado', 3.00]],
            ];
            foreach ($catalogue as $categoryName => $items) {
                $category = Category::query()->updateOrCreate(['business_id' => $business->id, 'name' => $categoryName]);
                foreach ($items as [$name, $price]) {
                    Product::query()->updateOrCreate(['business_id' => $business->id, 'name' => $name], ['category_id' => $category->id, 'price' => $price, 'stock' => in_array($categoryName, ['Cafés', 'Tés e infusiones'], true) ? null : 100]);
                }
            }
            $customers = collect([['María López', 'maria@example.com', '600 111 222'], ['Carlos Martín', 'carlos@example.com', '600 222 333'], ['Lucía Fernández', 'lucia@example.com', '600 333 444'], ['Oficinas Alameda SL', 'administracion@alameda.test', '910 555 010'], ['Peña Ciclista Norte', null, '600 444 555']])->map(fn (array $data): Customer => Customer::query()->updateOrCreate(['business_id' => $business->id, 'name' => $data[0]], ['email' => $data[1], 'phone' => $data[2]]));
            if ($business->sales()->doesntExist()) {
                $products = $business->products()->get();
                foreach (range(1, 45) as $index) {
                    $selected = $products->random(random_int(1, 4));
                    $selected = $selected instanceof Product ? collect([$selected]) : $selected;
                    $lines = $selected->map(function (Product $product): array {
                        $quantity = random_int(1, 3);
                        $unitPrice = (float) $product->price;

                        return compact('product', 'quantity', 'unitPrice') + ['lineTotal' => $quantity * $unitPrice];
                    });
                    $sale = $business->sales()->create(['customer_id' => random_int(1, 4) === 1 ? $customers->random()->id : null, 'total' => number_format($lines->sum('lineTotal'), 2, '.', ''), 'payment_method' => random_int(1, 3) === 1 ? 'card' : 'cash', 'checkout_token' => (string) Str::uuid(), 'sold_at' => now()->subDays(random_int(0, 20))->setTime(random_int(8, 21), random_int(0, 59))]);
                    foreach ($lines as $line) {
                        $sale->lines()->create(['product_id' => $line['product']->id, 'product_name' => $line['product']->name, 'quantity' => $line['quantity'], 'unit_price' => $line['unitPrice'], 'line_total' => $line['lineTotal']]);
                    }
                }
            }
        });
    }
}
