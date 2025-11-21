<?php

namespace App\Http\Controllers\Client\Api\Service;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;

class ListServicesController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $services = Service::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'price_type', 'price', 'max_quantity']);

        return response()->json([
            'services' => $services,
        ]);
    }
}
