<?php

namespace App\Http\Controllers\Admin\Logistics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoices\CancelInvoiceRequest;
use App\Http\Requests\Invoices\InvoiceLineRequest;
use App\Http\Requests\Invoices\InvoicePaymentRequest;
use App\Http\Requests\Invoices\StoreInvoiceRequest;
use App\Http\Requests\Invoices\UpdateInvoiceRequest;
use App\Mail\DocumentMail;
use App\Model\AtlasLogistics;
use App\Model\Currency;
use App\Model\Invoice;
use App\Model\InvoiceLine;
use App\Model\InvoicePayment;
use App\Model\User;
use App\Services\CurrencyService;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class InvoicesController extends Controller
{
    public function __construct(private InvoiceService $invoices)
    {
        $this->middleware('permission:Invoices list')->only(['index', 'show', 'pdf', 'printInvoice', 'email']);
        $this->middleware('permission:Invoices add')->only(['create', 'store']);
        $this->middleware('permission:Invoices edit')->only(['edit', 'update', 'storeLine', 'updateLine', 'destroyLine']);
        $this->middleware('permission:Invoices delete')->only(['destroy']);
        $this->middleware('permission:Invoices issue')->only(['issue']);
        $this->middleware('permission:Invoices cancel')->only(['cancel']);
        $this->middleware('permission:Invoices payments')->only(['storePayment', 'destroyPayment']);
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'client_id' => ['nullable', 'integer'],
            'currency_code' => ['nullable', 'string', 'size:3'],
            'document_type' => ['nullable', 'in:' . implode(',', array_keys(config('invoicing.document_types')))],
            'status' => ['nullable', 'in:draft,issued,part_paid,paid,cancelled'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $invoices = Invoice::with('client')
            ->when(!empty($filters['client_id']), fn ($q) => $q->where('client_id', $filters['client_id']))
            ->when(!empty($filters['currency_code']), fn ($q) => $q->where('currency_code', strtoupper($filters['currency_code'])))
            ->when(!empty($filters['document_type']), fn ($q) => $q->where('document_type', $filters['document_type']))
            ->when(!empty($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->when(!empty($filters['from']), fn ($q) => $q->whereDate('issue_date', '>=', $filters['from']))
            ->when(!empty($filters['to']), fn ($q) => $q->whereDate('issue_date', '<=', $filters['to']))
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->get();

        return view('invoices.index', [
            'invoices' => $invoices,
            'clients' => User::where('user_type', 'C')->whereNull('deleted_at')->orderBy('name')->get(['id', 'name']),
            'currencies' => Currency::where('is_active', true)->orderBy('code')->get(),
            'statuses' => config('invoicing.statuses'),
            'documentTypes' => config('invoicing.document_types'),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request)
    {
        $type = $request->input('type', old('document_type', Invoice::DOC_TAX_INVOICE));
        if (!array_key_exists($type, config('invoicing.document_types'))) {
            $type = Invoice::DOC_TAX_INVOICE;
        }

        return view('invoices.create', [
            'clients' => User::where('user_type', 'C')->whereNull('deleted_at')->orderBy('name')->get(['id', 'name']),
            'currencies' => Currency::where('is_active', true)->orderBy('code')->get(),
            'trips' => $this->invoices->unbilledTrips()->limit(500)->get(),
            'selectedTrips' => array_map('intval', (array) old('trip_ids', $request->input('trip_ids', []))),
            'selectedClient' => $request->input('client_id', old('client_id')),
            'documentTypes' => config('invoicing.document_types'),
            'documentType' => $type,
            'sourceInvoices' => Invoice::where('document_type', Invoice::DOC_TAX_INVOICE)
                ->whereIn('status', [Invoice::ISSUED, Invoice::PART_PAID, Invoice::PAID])
                ->orderByDesc('issue_date')->orderByDesc('id')
                ->get(['id', 'invoice_number', 'client_id', 'client_name', 'issue_date', 'total', 'currency_code']),
            'defaults' => $this->defaults(),
            'rates' => $this->currentRates(),
            'base' => CurrencyService::base(),
            'lineTypes' => config('invoicing.line_types'),
        ]);
    }

    public function store(StoreInvoiceRequest $request)
    {
        $invoice = $this->invoices->create($request->validated(), $request->user());

        return redirect()->route('logistics.invoices.show', $invoice)
            ->with('success', 'Draft invoice ' . $invoice->invoice_number . ' created. Review the lines, then issue it.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load([
            'lines.trip', 'payments.recordedBy', 'auditLogs.user', 'creator', 'issuer', 'canceller',
            'sourceInvoice', 'creditNotes',
        ]);

        return view('invoices.show', [
            'invoice' => $invoice,
            'paymentMethods' => config('logistics.payment_methods'),
        ]);
    }

    public function edit(Invoice $invoice)
    {
        abort_unless($invoice->isDraft(), 403, 'Issued invoices are locked. Cancel and reissue, or raise a credit note.');

        $invoice->load('lines.trip');

        return view('invoices.edit', [
            'invoice' => $invoice,
            'clients' => User::where('user_type', 'C')->whereNull('deleted_at')->orderBy('name')->get(['id', 'name']),
            'trips' => $this->invoices->billableTrips()->limit(500)->get(),
            'lineTypes' => config('invoicing.line_types'),
            'base' => CurrencyService::base(),
        ]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        $this->invoices->update($invoice, $request->validated(), $request->user());

        return redirect()->route('logistics.invoices.show', $invoice)->with('success', 'Invoice updated.');
    }

    public function destroy(Request $request, Invoice $invoice)
    {
        $this->invoices->deleteDraft($invoice, $request->user());

        return redirect()->route('logistics.invoices.index')->with('success', 'Draft invoice deleted.');
    }

    public function storeLine(InvoiceLineRequest $request, Invoice $invoice)
    {
        $this->invoices->addLine($invoice, $request->validated(), $request->user());

        return redirect()->route('logistics.invoices.edit', $invoice)->with('success', 'Line added.');
    }

    public function updateLine(InvoiceLineRequest $request, Invoice $invoice, InvoiceLine $line)
    {
        $this->invoices->updateLine($invoice, $line, $request->validated(), $request->user());

        return redirect()->route('logistics.invoices.edit', $invoice)->with('success', 'Line updated.');
    }

    public function destroyLine(Request $request, Invoice $invoice, InvoiceLine $line)
    {
        $this->invoices->removeLine($invoice, $line, $request->user());

        return redirect()->route('logistics.invoices.edit', $invoice)->with('success', 'Line removed.');
    }

    public function issue(Request $request, Invoice $invoice)
    {
        $this->invoices->issue($invoice, $request->user());

        return redirect()->route('logistics.invoices.show', $invoice)
            ->with('success', 'Invoice ' . $invoice->invoice_number . ' issued. It is now locked.');
    }

    public function cancel(CancelInvoiceRequest $request, Invoice $invoice)
    {
        $this->invoices->cancel($invoice, $request->validated()['reason'], $request->user());

        return redirect()->route('logistics.invoices.show', $invoice)->with('success', 'Invoice cancelled.');
    }

    public function storePayment(InvoicePaymentRequest $request, Invoice $invoice)
    {
        $this->invoices->recordPayment($invoice, $request->validated(), $request->user());

        return redirect()->route('logistics.invoices.show', $invoice)->with('success', 'Payment recorded.');
    }

    public function destroyPayment(Request $request, Invoice $invoice, InvoicePayment $payment)
    {
        $this->invoices->removePayment($invoice, $payment, $request->user());

        return redirect()->route('logistics.invoices.show', $invoice)->with('success', 'Payment removed.');
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load(['lines.trip', 'payments', 'sourceInvoice']);

        return Pdf::loadView('invoices.document', $this->documentData($invoice, false))
            ->setPaper('a4')
            ->download($invoice->invoice_number . '.pdf');
    }

    public function printInvoice(Invoice $invoice)
    {
        $invoice->load(['lines.trip', 'payments', 'sourceInvoice']);

        return view('invoices.document', $this->documentData($invoice, true));
    }

    public function email(Request $request, Invoice $invoice)
    {
        $to = $invoice->client_email ?: optional($invoice->client)->email;
        if (!$to) {
            return back()->with('error', 'This customer has no email address on file.');
        }

        try {
            $invoice->load(['lines.trip', 'payments', 'sourceInvoice']);
            $pdf = Pdf::loadView('invoices.document', $this->documentData($invoice, false))->setPaper('a4');

            Mail::to($to)->send(new DocumentMail(
                mailSubject: $invoice->document_type_label . ' ' . $invoice->invoice_number,
                customerName: $invoice->client_name,
                documentLabel: $invoice->document_type_label,
                documentNumber: $invoice->invoice_number,
                intro: 'Please find your ' . strtolower($invoice->document_type_label) . ' attached to this email.',
                attachmentData: $pdf->output(),
                attachmentName: $invoice->invoice_number . '.pdf',
                attachmentMime: 'application/pdf',
            ));
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'The email could not be sent. Check the mail configuration and try again.');
        }

        return back()->with('success', $invoice->document_type_label . ' emailed to ' . $to . '.');
    }

    private function defaults(): array
    {
        return [
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays((int) config('invoicing.default_due_days', 30))->toDateString(),
            'currency_code' => CurrencyService::base(),
            'tax_rate' => '0',
            'exchange_rate' => '',
            'payment_instructions' => AtlasLogistics::get('invoice_text'),
        ];
    }

    private function currentRates(): array
    {
        if (!Currency::where('is_active', true)->exists()) {
            return [];
        }

        $service = app(CurrencyService::class);
        $out = [];
        foreach (Currency::where('is_active', true)->get() as $currency) {
            $rate = $currency->code === CurrencyService::base() ? 1 : $service->rateFor($currency->code);
            $out[$currency->code] = $rate ? \App\Support\Money::roundDecimal($rate, 6) : '';
        }

        return $out;
    }

    private function documentData(Invoice $invoice, bool $print): array
    {
        return [
            'invoice' => $invoice,
            'print' => $print,
            'base' => CurrencyService::base(),
            'company' => [
                'name' => AtlasLogistics::get('app_name') ?: 'Atlas Logistics',
                'address1' => AtlasLogistics::get('badd1'),
                'address2' => AtlasLogistics::get('badd2'),
                'city' => AtlasLogistics::get('city'),
                'state' => AtlasLogistics::get('state'),
                'country' => AtlasLogistics::get('country'),
                'email' => AtlasLogistics::get('email'),
                'phone' => AtlasLogistics::get('phone'),
                'tax_no' => AtlasLogistics::get('tax_no'),
                'logo' => $this->logoDataUri(),
            ],
        ];
    }

    private function logoDataUri(): ?string
    {
        $logo = AtlasLogistics::get('logo_img');
        if (!$logo) {
            return null;
        }

        $path = public_path('assets/images/' . $logo);
        if (!is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }
}
