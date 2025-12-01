<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use h4kuna\Ares\AresFactory;
use h4kuna\Ares\Exceptions\IdentificationNumberNotFoundException;
use Illuminate\Http\JsonResponse;

class AresController extends Controller
{
    public function show(string $ico): JsonResponse
    {
        $ares = (new AresFactory)->create();

        try {
            $response = $ares->loadBasic($ico);
        } catch (IdentificationNumberNotFoundException $e) {
            return response()->json(['message' => 'IČO nebylo nalezeno.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Chyba při komunikaci s ARES.'], 500);
        }

        return response()->json([
            'ico' => $response->ico,
            'dic' => $response->tin,
            'company_name' => $response->company,
            'street' => $response->street.' '.$response->street_number,
            'city' => $response->city,
            'zip' => $response->zip,
            'country' => 'CZ', // ARES is primarily CZ
            'is_vat_payer' => (bool) $response->tin, // Simplistic assumption: if TIN exists, they might be VAT payer. ARES usually returns TIN for payers.
        ]);
    }
}
