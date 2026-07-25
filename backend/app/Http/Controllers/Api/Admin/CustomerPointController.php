<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * SALON-OPS-ENHANCEMENTS.md, "Customers" (Tier 3) — points ledger. Earning
 * off job payments happens automatically (JobController::addPayment); this
 * controller is for reading the ledger back and for the two kinds of entry
 * an admin makes on purpose — redeeming points against a reward, or a
 * manual adjustment (goodwill bonus, or fixing a mistaken entry). Same
 * shape as Api\Admin\StockMovementController.
 */
class CustomerPointController extends Controller
{
    public function index(Request $request, Customer $customer): JsonResponse
    {
        $ledger = $customer->pointsLedger()
            ->with('creator:id,name')
            ->paginate(25);

        return response()->json(['ledger' => $ledger, 'pointsBalance' => $customer->points_balance]);
    }

    public function store(Request $request, Customer $customer): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:earned,redeemed,adjustment'],
            'points' => ['required', 'integer', 'not_in:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        // Same "the type tells you the direction" convention as the stock
        // ledger's restock/adjustment split — earned/redeemed always move
        // in a fixed direction so the type alone is unambiguous; only a
        // free-form adjustment can go either way.
        if ($data['type'] === 'earned' && $data['points'] < 0) {
            return response()->json([
                'message' => 'Earned points must be positive — use an adjustment for a downward correction.',
                'errors' => ['points' => ['Earned points must be positive.']],
            ], 422);
        }

        if ($data['type'] === 'redeemed') {
            $magnitude = abs($data['points']);
            if ($magnitude > $customer->points_balance) {
                return response()->json([
                    'message' => "{$customer->name} only has {$customer->points_balance} points — can't redeem {$magnitude}.",
                    'errors' => ['points' => ['Not enough points balance.']],
                ], 422);
            }
        }

        $entry = match ($data['type']) {
            'earned' => $customer->earnPoints($data['points'], $data['reason'] ?? null, null, null, Auth::id()),
            'redeemed' => $customer->redeemPoints(abs($data['points']), $data['reason'] ?? null, Auth::id()),
            'adjustment' => $customer->adjustPoints($data['points'], $data['reason'] ?? null, Auth::id()),
        };

        ActivityLogger::log(
            'customer.points_'.$data['type'],
            sprintf(
                '%s %s%d points for %s%s',
                ucfirst($data['type']),
                $entry->points > 0 ? '+' : '',
                $entry->points,
                $customer->name,
                $data['reason'] ? " ({$data['reason']})" : ''
            ),
            $customer,
            ['ledger_id' => $entry->id]
        );

        return response()->json([
            'entry' => $entry,
            'customer' => $customer->fresh(),
            'message' => 'Points updated.',
        ], 201);
    }
}
