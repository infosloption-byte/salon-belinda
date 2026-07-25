<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * SALON-OPS-ENHANCEMENTS.md, "Inventory" (Tier 3) — stock movement ledger.
 * Sales (OrderController) and raw product-form edits (ProductController)
 * both log here automatically; this controller is for the two kinds of
 * movement an admin triggers on purpose — restocking and write-off/
 * correction adjustments — plus reading the ledger back per product.
 */
class StockMovementController extends Controller
{
    public function index(Request $request, Product $product): JsonResponse
    {
        $movements = $product->stockMovements()
            ->with('creator:id,name')
            ->paginate(25);

        return response()->json(['movements' => $movements]);
    }

    public function store(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:restock,adjustment'],
            'quantity_change' => ['required', 'integer', 'not_in:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        // Restock is stock coming in (always positive) — a downward
        // correction (breakage, loss, stocktake write-off) is an
        // 'adjustment' instead, so the ledger type itself tells you the
        // direction without reading the sign.
        if ($data['type'] === 'restock' && $data['quantity_change'] < 0) {
            return response()->json([
                'message' => 'Restock quantity must be positive — use an adjustment for a downward correction.',
                'errors' => ['quantity_change' => ['Restock quantity must be positive.']],
            ], 422);
        }

        $movement = $product->applyMovement($data['type'], $data['quantity_change'], $data['reason'] ?? null, Auth::id());

        ActivityLogger::log(
            'product.stock_movement',
            sprintf(
                '%s %s%d on "%s"%s',
                ucfirst($data['type']),
                $data['quantity_change'] > 0 ? '+' : '',
                $data['quantity_change'],
                $product->name,
                $data['reason'] ? " ({$data['reason']})" : ''
            ),
            $product,
            ['movement_id' => $movement->id]
        );

        return response()->json([
            'movement' => $movement,
            'product' => $product->fresh(),
            'message' => 'Stock updated.',
        ], 201);
    }
}
