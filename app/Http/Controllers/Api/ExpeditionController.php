<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expedition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpeditionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $expeditions = Expedition::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (Expedition $expedition) use ($request) {
                $quantity = max(1, (int) $request->integer('quantity', 1));
                $extraWeightFee = max(0, $quantity - 1) * 1000;

                return [
                    'id' => $expedition->id,
                    'name' => $expedition->name,
                    'code' => $expedition->code,
                    'service' => $expedition->service,
                    'base_cost' => (float) $expedition->base_cost,
                    'shipping_cost' => (float) $expedition->base_cost + $extraWeightFee,
                    'estimated_days' => $expedition->estimated_days,
                ];
            });

        return response()->json([
            'expeditions' => $expeditions,
        ]);
    }
}
