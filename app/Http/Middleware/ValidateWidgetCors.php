<?php

namespace App\Http\Middleware;

use App\Models\Widget;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateWidgetCors
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Extract UUID from route parameter
        $uuid = $request->route('uuid');

        if (! $uuid) {
            return response()->json(['message' => 'Widget UUID required'], 400);
        }

        // Find the widget
        $widget = Widget::where('uuid', $uuid)
            ->where('is_active', true)
            ->first();

        if (! $widget) {
            return response()->json(['message' => 'Widget not found or inactive'], 404);
        }

        // Get the Origin header
        $origin = $request->header('Origin');

        // Check CORS if allowed_domains is set and not empty
        $allowedDomains = $widget->allowed_domains ?? [];

        if (! empty($allowedDomains) && $origin) {
            // Check if origin matches any allowed domain
            $isAllowed = false;

            foreach ($allowedDomains as $allowedDomain) {
                // Normalize domains for comparison
                $normalizedOrigin = rtrim($origin, '/');
                $normalizedAllowedDomain = rtrim($allowedDomain, '/');

                if ($normalizedOrigin === $normalizedAllowedDomain) {
                    $isAllowed = true;
                    break;
                }
            }

            if (! $isAllowed) {
                return response()->json([
                    'message' => 'CORS policy: Origin not allowed',
                ], 403);
            }
        }

        // Process the request
        $response = $next($request);

        // Set CORS headers on the response
        if ($origin) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        } else {
            // If no origin header and allowed_domains is empty, allow all
            if (empty($allowedDomains)) {
                $response->headers->set('Access-Control-Allow-Origin', '*');
                $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
                $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');
            }
        }

        return $response;
    }
}
