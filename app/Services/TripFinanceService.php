<?php

namespace App\Services;

use App\Model\Invoice;
use App\Model\InvoiceLine;
use App\Model\Trip;
use App\Support\Money;

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
        // Allowance advances are a trip cost. Fuel/border advances stay out of cost because the
        // matching expense claim captures the actual spend.
        $allowances    = (float) $trip->advances->where('purpose', 'allowance')->sum('amount_base');
        $totalCost     = $expenseTotal + $incidentTotal + $allowances;

        // Issued invoices replace the manually entered trip revenue on an accrual basis.
        // Each line is converted with the rate frozen on its own invoice.
        $invoicedMinor = 0;
        $invoiceLines = InvoiceLine::where('trip_id', $trip->id)
            ->whereHas('invoice', fn ($q) => $q->whereIn('status', Invoice::REVENUE_STATUSES))
            ->with('invoice:id,exchange_rate')
            ->get();
        foreach ($invoiceLines as $line) {
            $invoicedMinor += Money::applyRate(Money::toMinor($line->line_total), (string) $line->invoice->exchange_rate);
        }

        $revenueMinor  = $invoicedMinor > 0 ? $invoicedMinor : Money::toMinor($trip->revenue_amount_base);
        $revenue       = $revenueMinor / 100;
        $profit        = $revenue - $totalCost;
        $km            = (float) $trip->distance_km;

        $driverCash = (float) $approved->where('paid_by', 'driver_cash')->sum('amount_base');
        $advances   = (float) $trip->advances->sum('amount_base');

        return [
            'revenue'         => $revenue,
            'invoiced_revenue' => $invoicedMinor / 100,
            'expenses'        => $expenseTotal,
            'incidents'       => $incidentTotal,
            'allowances'      => $allowances,
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
