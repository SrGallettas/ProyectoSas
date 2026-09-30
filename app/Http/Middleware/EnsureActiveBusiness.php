<?php

namespace App\Http\Middleware;

use App\Models\Business;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveBusiness
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $activeBusiness = $request->user()
            ->businesses()
            ->find($request->session()->get('active_business_id'));

        if ($activeBusiness === null) {
            $request->session()->forget('active_business_id');

            return redirect()
                ->route('businesses.index')
                ->with('status', 'Selecciona un comercio antes de continuar.');
        }

        $request->attributes->set('activeBusiness', $activeBusiness);

        $this->scopeRouteModel($request, $activeBusiness, 'product', 'products');
        $this->scopeRouteModel($request, $activeBusiness, 'customer', 'customers');
        $this->scopeRouteModel($request, $activeBusiness, 'category', 'categories');
        $this->scopeRouteModel($request, $activeBusiness, 'sale', 'sales');

        return $next($request);
    }

    private function scopeRouteModel(
        Request $request,
        Business $activeBusiness,
        string $parameter,
        string $relationship,
    ): void {
        $routeModel = $request->route($parameter);

        if ($routeModel === null) {
            return;
        }

        $modelId = $routeModel instanceof Model ? $routeModel->getKey() : $routeModel;
        $model = $activeBusiness->{$relationship}()->findOrFail($modelId);

        $request->route()->setParameter($parameter, $model);
    }
}
