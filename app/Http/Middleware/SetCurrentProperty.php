<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Schema;

class SetCurrentProperty
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->current_property_id) {
            if (Schema::hasTable('properties') && Schema::hasColumn('users', 'current_property_id')) {
                $firstProperty = $user->properties()->first();

                if ($firstProperty) {
                    $user->forceFill([
                        'current_property_id' => $firstProperty->id,
                    ])->save();
                }
            }
        }

        return $next($request);
    }
}
