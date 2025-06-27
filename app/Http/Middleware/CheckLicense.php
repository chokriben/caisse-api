<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class CheckLicense
{
    public function handle(Request $request, Closure $next)
    {
        $expireDate = Config::get('app.license_expire_date');

        if (!$expireDate) {
            abort(403, "Licence non définie.");
        }

        if (Carbon::now()->gt(Carbon::parse($expireDate))) {
            return response()->json([
                'message' => 'Votre licence a expiré. Veuillez la renouveler.'
            ], 403);
        }

        return $next($request);
    }
}
