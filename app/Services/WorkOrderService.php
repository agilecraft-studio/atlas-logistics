<?php

namespace App\Services;

use App\Model\PartsModel;
use App\Model\PartsUsedModel;
use App\Model\WorkOrders;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Stock-safe work-order parts handling.
 *
 * Every method locks the part row before touching stock. Call inside a
 * DB::transaction() so a failed line leaves both the order and stock untouched.
 */
class WorkOrderService
{
    /** @param array<int|string, int|string> $parts part_id => quantity */
    public function deductParts(WorkOrders $order, array $parts): void
    {
        foreach ($parts as $partId => $qty) {
            if (!is_numeric($partId)) {
                continue;
            }
            $qty = (int) $qty;
            if ($qty < 1) {
                continue;
            }

            $part = PartsModel::whereKey($partId)->lockForUpdate()->first();
            if (!$part) {
                throw ValidationException::withMessages(['parts' => 'Part #' . $partId . ' no longer exists.']);
            }
            if ((int) $part->stock < $qty) {
                throw ValidationException::withMessages([
                    'parts' => $part->title . ' only has ' . (int) $part->stock . ' in stock.',
                ]);
            }

            PartsUsedModel::create([
                'work_id' => $order->id,
                'part_id' => $part->id,
                'qty' => $qty,
                'price' => $part->unit_cost,
                'total' => $qty * (float) $part->unit_cost,
            ]);

            $part->stock = (int) $part->stock - $qty;
            $part->availability = $part->stock > 0 ? 1 : 0;
            $part->save();
        }
    }

    /** Return every still-deducted part to stock exactly once. */
    public function restoreParts(WorkOrders $order): void
    {
        $used = PartsUsedModel::where('work_id', $order->id)->whereNull('restored_at')->get();

        foreach ($used as $row) {
            $part = PartsModel::whereKey($row->part_id)->lockForUpdate()->first();
            if ($part) {
                $part->stock = (int) $part->stock + (int) $row->qty;
                $part->availability = $part->stock > 0 ? 1 : 0;
                $part->save();
            }
            $row->restored_at = now();
            $row->save();
        }
    }

    public function archive(WorkOrders $order): void
    {
        DB::transaction(function () use ($order) {
            $this->restoreParts($order);
            $order->delete();
        });
    }
}
