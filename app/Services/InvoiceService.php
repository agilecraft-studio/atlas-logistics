<?php

namespace App\Services;

use App\Model\AtlasLogistics;
use App\Model\Currency;
use App\Model\Invoice;
use App\Model\InvoiceAuditLog;
use App\Model\InvoiceLine;
use App\Model\InvoicePayment;
use App\Model\Trip;
use App\Model\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Invoice lifecycle. Money is calculated in integer minor units (see App\Support\Money); the
 * exchange rate is frozen on the invoice at issue, so later rate changes never rewrite history.
 */
class InvoiceService
{
    public function create(array $data, User $user): Invoice
    {
        return DB::transaction(function () use ($data, $user) {
            $client = User::where('user_type', 'C')->findOrFail($data['client_id']);
            $currency = Currency::where('code', strtoupper($data['currency_code']))->where('is_active', true)->firstOrFail();
            $issueDate = $data['issue_date'] ?: now()->toDateString();
            $rate = $this->resolveRate($currency->code, $data['exchange_rate'] ?? null, $issueDate, true, true);

            $invoice = Invoice::create(array_merge($this->clientSnapshot($client), [
                'invoice_number'       => $this->nextNumber(),
                'status'               => Invoice::DRAFT,
                'currency_code'        => $currency->code,
                'currency_symbol'      => $currency->symbol,
                'base_currency_code'   => CurrencyService::base(),
                'exchange_rate'        => $rate,
                'rate_locked'          => (bool) ($data['rate_locked'] ?? false),
                'tax_rate'             => Money::roundDecimal($data['tax_rate'] ?? 0, 4),
                'issue_date'           => $issueDate,
                'due_date'             => $data['due_date'] ?: null,
                'payment_instructions' => $data['payment_instructions'] ?? AtlasLogistics::get('invoice_text'),
                'notes'                => $data['notes'] ?? null,
                'created_by'           => $user->id,
            ]));

            $sort = 0;
            foreach ($this->tripsFor($data['trip_ids'] ?? []) as $trip) {
                if ($this->tripHasLiveFreight($trip->id)) {
                    throw ValidationException::withMessages([
                        'trip_ids' => "Trip {$trip->trip_number} already has a freight line on a live invoice. Add extra charges as manual lines instead.",
                    ]);
                }
                $unitPrice = $this->freightInInvoiceCurrency($trip, $currency->code, $rate);
                $invoice->lines()->create([
                    'trip_id'     => $trip->id,
                    'line_type'   => 'freight',
                    'description' => 'Freight — ' . $trip->trip_number . ': ' . $trip->route_label,
                    'quantity'    => '1',
                    'unit_price'  => Money::fromMinor($unitPrice),
                    'line_total'  => Money::fromMinor($unitPrice),
                    'sort_order'  => $sort++,
                ]);
            }

            $this->recalculate($invoice);
            $this->audit($invoice, 'created', ['currency' => $currency->code, 'rate' => $rate], $user);

            return $invoice->fresh(['lines']);
        });
    }

    public function update(Invoice $invoice, array $data, User $user): Invoice
    {
        return DB::transaction(function () use ($invoice, $data, $user) {
            $invoice = $this->lockDraft($invoice);
            $client = User::where('user_type', 'C')->findOrFail($data['client_id']);

            $issueDate = $data['issue_date'] ?: $invoice->issue_date?->toDateString();
            $explicitRate = $data['exchange_rate'] ?? null;
            $locked = (bool) ($data['rate_locked'] ?? false);

            $rate = $locked
                ? $this->resolveRate($invoice->currency_code, $explicitRate, $issueDate, true, true)
                : $this->resolveRate($invoice->currency_code, $explicitRate ?: $invoice->exchange_rate, $issueDate, true, true);

            $invoice->fill(array_merge($this->clientSnapshot($client), [
                'issue_date'           => $issueDate,
                'due_date'             => $data['due_date'] ?: null,
                'tax_rate'             => Money::roundDecimal($data['tax_rate'] ?? 0, 4),
                'exchange_rate'        => $rate,
                'rate_locked'          => $locked,
                'payment_instructions' => $data['payment_instructions'] ?? null,
                'notes'                => $data['notes'] ?? null,
            ]))->save();

            $this->recalculate($invoice);
            $this->audit($invoice, 'updated', ['rate' => $rate, 'tax_rate' => $invoice->tax_rate], $user);

            return $invoice->fresh(['lines']);
        });
    }

    public function addLine(Invoice $invoice, array $data, User $user): InvoiceLine
    {
        return DB::transaction(function () use ($invoice, $data, $user) {
            $invoice = $this->lockDraft($invoice);
            $line = $invoice->lines()->create($this->lineAttributes($invoice, $data, (int) $invoice->lines()->max('sort_order') + 1));
            $this->recalculate($invoice);
            $this->audit($invoice, 'line_added', ['line_id' => $line->id, 'description' => $line->description], $user);

            return $line;
        });
    }

    public function updateLine(Invoice $invoice, InvoiceLine $line, array $data, User $user): InvoiceLine
    {
        return DB::transaction(function () use ($invoice, $line, $data, $user) {
            $invoice = $this->lockDraft($invoice);
            abort_unless((int) $line->invoice_id === (int) $invoice->id, 404);

            $line->fill($this->lineAttributes($invoice, $data, (int) $line->sort_order))->save();
            $this->recalculate($invoice);
            $this->audit($invoice, 'line_updated', ['line_id' => $line->id, 'description' => $line->description], $user);

            return $line;
        });
    }

    public function removeLine(Invoice $invoice, InvoiceLine $line, User $user): void
    {
        DB::transaction(function () use ($invoice, $line, $user) {
            $invoice = $this->lockDraft($invoice);
            abort_unless((int) $line->invoice_id === (int) $invoice->id, 404);

            $this->audit($invoice, 'line_removed', ['description' => $line->description, 'line_total' => $line->line_total], $user);
            $line->delete();
            $this->recalculate($invoice);
        });
    }

    public function issue(Invoice $invoice, User $user): Invoice
    {
        return DB::transaction(function () use ($invoice, $user) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            if (!$invoice->isDraft()) {
                throw ValidationException::withMessages(['status' => 'Only a draft invoice can be issued.']);
            }

            $invoice->loadCount('lines');
            if ($invoice->lines_count === 0) {
                throw ValidationException::withMessages(['lines' => 'Add at least one invoice line before issuing.']);
            }

            $issueDate = $invoice->issue_date ?: now()->toDateString();
            $rate = $invoice->rate_locked
                ? $this->resolveRate($invoice->currency_code, $invoice->exchange_rate, $issueDate, true, true)
                : $this->resolveRate($invoice->currency_code, $invoice->exchange_rate, $issueDate, true, false);

            if ($invoice->client) {
                $invoice->fill($this->clientSnapshot($invoice->client));
            }
            $invoice->issue_date = $issueDate;
            $invoice->exchange_rate = $rate;
            $invoice->save();

            $this->recalculate($invoice);
            if (Money::toMinor($invoice->total) <= 0) {
                throw ValidationException::withMessages(['total' => 'The invoice total must be greater than zero.']);
            }

            $invoice->status = Invoice::ISSUED;
            $invoice->issued_at = now();
            $invoice->issued_by = $user->id;
            $invoice->save();

            $this->audit($invoice, 'issued', ['rate' => $rate, 'total' => $invoice->total], $user);

            return $invoice->fresh();
        });
    }

    public function cancel(Invoice $invoice, string $reason, User $user): Invoice
    {
        return DB::transaction(function () use ($invoice, $reason, $user) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if (!in_array($invoice->status, [Invoice::ISSUED, Invoice::PART_PAID], true)) {
                throw ValidationException::withMessages(['status' => 'Only an issued or part-paid invoice can be cancelled.']);
            }
            if (Money::toMinor($invoice->amount_paid) > 0) {
                throw ValidationException::withMessages(['status' => 'Payments have been recorded. Raise a credit note instead of cancelling.']);
            }

            $invoice->update([
                'status'        => Invoice::CANCELLED,
                'cancelled_at'  => now(),
                'cancelled_by'  => $user->id,
                'cancel_reason' => $reason,
            ]);
            $this->audit($invoice, 'cancelled', ['reason' => $reason], $user);

            return $invoice->fresh();
        });
    }

    public function recordPayment(Invoice $invoice, array $data, User $user): InvoicePayment
    {
        return DB::transaction(function () use ($invoice, $data, $user) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            if (!$invoice->canTakePayments()) {
                throw ValidationException::withMessages(['amount' => 'This invoice cannot take payments in its current status.']);
            }

            $amount = Money::toMinor($data['amount']);
            $balance = Money::toMinor($invoice->balance_due);
            if ($amount <= 0) {
                throw ValidationException::withMessages(['amount' => 'Enter a payment amount greater than zero.']);
            }
            if ($amount > $balance) {
                throw ValidationException::withMessages(['amount' => 'The payment cannot exceed the balance due.']);
            }

            $payment = $invoice->payments()->create([
                'amount'        => Money::fromMinor($amount),
                'currency_code' => $invoice->currency_code,
                'paid_at'       => $data['paid_at'] ?: now()->toDateString(),
                'method'        => $data['method'],
                'reference'     => $data['reference'] ?? null,
                'notes'         => $data['notes'] ?? null,
                'recorded_by'   => $user->id,
            ]);

            $this->recalculate($invoice);
            $this->audit($invoice, 'payment_recorded', [
                'payment_id' => $payment->id,
                'amount'     => $payment->amount,
                'method'     => $payment->method,
            ], $user);

            return $payment;
        });
    }

    public function removePayment(Invoice $invoice, InvoicePayment $payment, User $user): void
    {
        DB::transaction(function () use ($invoice, $payment, $user) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $payment->invoice_id === (int) $invoice->id, 404);

            if (!in_array($invoice->status, [Invoice::ISSUED, Invoice::PART_PAID, Invoice::PAID], true)) {
                throw ValidationException::withMessages(['status' => 'This invoice cannot have payments removed.']);
            }

            $this->audit($invoice, 'payment_removed', [
                'payment_id' => $payment->id,
                'amount'     => $payment->amount,
            ], $user);
            $payment->delete();
            $this->recalculate($invoice);
        });
    }

    public function deleteDraft(Invoice $invoice, User $user): void
    {
        DB::transaction(function () use ($invoice, $user) {
            $invoice = $this->lockDraft($invoice);
            $this->audit($invoice, 'deleted', ['invoice_number' => $invoice->invoice_number], $user);
            $invoice->delete();
        });
    }

    /** Recompute line totals, subtotal, tax, amount paid, balance and status. */
    public function recalculate(Invoice $invoice): Invoice
    {
        $subtotalMinor = 0;
        foreach ($invoice->lines()->get() as $line) {
            $lineTotalMinor = Money::multiplyQuantity(Money::toMinor($line->unit_price), $line->quantity);
            $lineTotal = Money::fromMinor($lineTotalMinor);
            if ((string) $line->line_total !== $lineTotal) {
                $line->line_total = $lineTotal;
                $line->save();
            }
            $subtotalMinor += $lineTotalMinor;
        }

        $taxMinor = Money::percent($subtotalMinor, $invoice->tax_rate ?: 0);
        $totalMinor = $subtotalMinor + $taxMinor;

        $paidMinor = 0;
        foreach ($invoice->payments()->get() as $payment) {
            $paidMinor += Money::toMinor($payment->amount);
        }

        $balanceMinor = max(0, $totalMinor - $paidMinor);

        $invoice->forceFill([
            'subtotal'     => Money::fromMinor($subtotalMinor),
            'tax_amount'   => Money::fromMinor($taxMinor),
            'total'        => Money::fromMinor($totalMinor),
            'total_base'   => Money::fromMinor(Money::applyRate($totalMinor, $invoice->exchange_rate ?: '1')),
            'amount_paid'  => Money::fromMinor($paidMinor),
            'balance_due'  => Money::fromMinor($balanceMinor),
        ]);

        if (in_array($invoice->status, Invoice::REVENUE_STATUSES, true)) {
            $invoice->status = $paidMinor <= 0
                ? Invoice::ISSUED
                : ($paidMinor < $totalMinor ? Invoice::PART_PAID : Invoice::PAID);
        }

        $invoice->save();

        return $invoice;
    }

    public function audit(Invoice $invoice, string $action, array $details = [], ?User $user = null): void
    {
        InvoiceAuditLog::create([
            'invoice_id' => $invoice->id,
            'user_id'    => ($user ?: auth()->user())?->id,
            'action'     => $action,
            'details'    => $details ?: null,
        ]);
    }

    /** Completed trips that can be billed, newest first. */
    public function billableTrips()
    {
        return Trip::with(['originCountry', 'destinationCountry'])
            ->whereIn('status', ['completed', 'closed'])
            ->orderByDesc('actual_end_at')
            ->orderByDesc('id');
    }

    /** Completed trips without a freight line on a live (non-cancelled) invoice. */
    public function unbilledTrips()
    {
        return $this->billableTrips()
            ->whereDoesntHave('invoiceLines', fn ($q) => $q
                ->where('line_type', 'freight')
                ->whereHas('invoice', fn ($i) => $i->where('status', '!=', Invoice::CANCELLED)));
    }

    private function tripHasLiveFreight(int $tripId): bool
    {
        return InvoiceLine::where('trip_id', $tripId)
            ->where('line_type', 'freight')
            ->whereHas('invoice', fn ($q) => $q->where('status', '!=', Invoice::CANCELLED))
            ->exists();
    }

    private function tripsFor(array $tripIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $tripIds))));
        if (!$ids) {
            return [];
        }

        $trips = Trip::with(['originCountry', 'destinationCountry'])
            ->whereIn('status', ['completed', 'closed'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return array_values(array_filter(array_map(fn ($id) => $trips->get($id), $ids)));
    }

    private function freightInInvoiceCurrency(Trip $trip, string $invoiceCurrency, string $rate): int
    {
        $invoiceCurrency = strtoupper($invoiceCurrency);
        if (strtoupper((string) $trip->revenue_currency) === $invoiceCurrency) {
            return Money::toMinor($trip->revenue_amount);
        }

        $baseMinor = Money::toMinor($trip->revenue_amount_base);
        if ($baseMinor === 0 && Money::toMinor($trip->revenue_amount) > 0) {
            $baseMinor = Money::applyRate(Money::toMinor($trip->revenue_amount), $trip->revenue_rate ?: '1');
        }

        return $invoiceCurrency === CurrencyService::base()
            ? $baseMinor
            : Money::divideByRate($baseMinor, $rate);
    }

    private function lineAttributes(Invoice $invoice, array $data, int $sortOrder): array
    {
        $unitPrice = Money::toMinor($data['unit_price']);
        $total = Money::multiplyQuantity($unitPrice, $data['quantity']);

        $tripId = $data['trip_id'] ?? null;
        if ($tripId !== null && $tripId !== '') {
            $tripId = (int) $tripId;
            $belongs = Trip::whereIn('status', ['completed', 'closed'])->whereKey($tripId)->exists();
            if (!$belongs) {
                throw ValidationException::withMessages(['trip_id' => 'Choose a completed trip.']);
            }
        } else {
            $tripId = null;
        }

        return [
            'trip_id'     => $tripId,
            'line_type'   => $data['line_type'],
            'description' => $data['description'],
            'quantity'    => Money::roundDecimal($data['quantity'], 4),
            'unit_price'  => Money::fromMinor($unitPrice),
            'line_total'  => Money::fromMinor($total),
            'sort_order'  => $sortOrder,
        ];
    }

    private function lockDraft(Invoice $invoice): Invoice
    {
        $locked = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
        if (!$locked->isDraft()) {
            throw ValidationException::withMessages(['status' => 'Issued invoices are locked. Cancel and reissue, or raise a credit note.']);
        }

        return $locked;
    }

    private function clientSnapshot(User $client): array
    {
        return [
            'client_id'      => $client->id,
            'client_name'    => $client->name,
            'client_email'   => $client->email,
            'client_phone'   => $client->getMeta('mobno') ?: $client->getMeta('phone'),
            'client_address' => $client->getMeta('address'),
        ];
    }

    private function resolveRate(string $currencyCode, $explicit, $date, bool $required, bool $preferExplicit): string
    {
        $currencyCode = strtoupper($currencyCode);
        $base = CurrencyService::base();

        if ($currencyCode === $base) {
            return '1.000000';
        }

        $hasExplicit = $explicit !== null && $explicit !== '' && is_numeric($explicit) && (float) $explicit > 0;
        if ($preferExplicit && $hasExplicit) {
            return Money::roundDecimal($explicit, 6);
        }

        $resolved = app(CurrencyService::class)->rateFor($currencyCode, $date ?: now()->toDateString());
        if ($resolved !== null && $resolved > 0) {
            return Money::roundDecimal($resolved, 6);
        }

        if ($hasExplicit) {
            return Money::roundDecimal($explicit, 6);
        }

        if ($required) {
            throw ValidationException::withMessages([
                'exchange_rate' => "No exchange rate found for {$currencyCode}. Add one under Master Data > Exchange Rates or enter a rate on this invoice.",
            ]);
        }

        return '1.000000';
    }

    private function nextNumber(): string
    {
        $prefix = (string) config('invoicing.prefix', 'INV');
        $year = (int) now()->year;
        $now = now();

        DB::table('invoice_sequences')->insertOrIgnore([
            'prefix' => $prefix, 'year' => $year, 'last_number' => 0, 'created_at' => $now, 'updated_at' => $now,
        ]);

        $row = DB::table('invoice_sequences')
            ->where('prefix', $prefix)->where('year', $year)
            ->lockForUpdate()->first();

        $next = ((int) $row->last_number) + 1;
        DB::table('invoice_sequences')
            ->where('prefix', $prefix)->where('year', $year)
            ->update(['last_number' => $next, 'updated_at' => $now]);

        return sprintf('%s-%d-%04d', $prefix, $year, $next);
    }
}
