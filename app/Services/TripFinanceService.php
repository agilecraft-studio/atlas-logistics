<?php

namespace App\Services;

use App\Model\Trip;

class TripFinanceService
{
    /**
     * Profit & reconciliation numbers for one trip (all in base currency).
     * Costs = APPROVED expenses + incident costs. Pending expenses are shown separately.
     */
    public function summary(Trip $trip): array
    {
        $trip->loadMissing(['expenses.category', 'advances', 'incidents']);

        $approved = $trip->expenses->where('status', 'approved');
        $pending  = $trip->expenses->where('status', 'pending');

        $expenseTotal  = (float) $approved->sum('amount_base');
        $incidentTotal = (float) $trip->incidents->sum('cost_base');
        $totalCost     = $expenseTotal + $incidentTotal;
        $revenue       = (float) $trip->revenue_amount_base;
        $profit        = $revenue - $totalCost;
        $km            = (float) $trip->distance_km;

        $driverCash = (float) $approved->where('paid_by', 'driver_cash')->sum('amount_base');
        $advances   = (float) $trip->advances->sum('amount_base');

        return [
            'revenue'         => $revenue,
            'expenses'        => $expenseTotal,
            'incidents'       => $incidentTotal,
            'total_cost'      => $totalCost,
            'profit'          => $profit,
            'margin'          => $revenue > 0 ? round($profit / $revenue * 100, 1) : null,
            'pending'         => (float) $pending->sum('amount_base'),
            'pending_count'   => $pending->count(),
            'cost_per_km'     => $km > 0 ? round($totalCost / $km, 2) : null,
            'revenue_per_km'  => $km > 0 ? round($revenue / $km, 2) : null,
            'advances'        => $advances,
            'driver_cash'     => $driverCash,
            // > 0: driver still holds unspent cash; < 0: driver spent own money (company owes driver)
            'advance_balance' => round($advances - $driverCash, 2),
            'by_category'     => $approved->groupBy(fn ($e) => optional($e->category)->name ?: 'Other')
                                    ->map(fn ($g) => (float) $g->sum('amount_base'))->sortDesc()->all(),
        ];
    }
}
