<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\DocumentMail;
use App\Model\Currency;
use App\Model\CustomerDocument;
use App\Model\Trip;
use App\Model\User;
use App\Services\CustomerDocumentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Customer hub documents (delivery notes, dispatch sheets, waybills, quotes and PoD uploads).
 * Every document is scoped to the customer in the URL and may be linked to one of their trips.
 */
class CustomerDocumentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Customer documents list')->only(['show', 'download', 'print', 'email']);
        $this->middleware('permission:Customer documents add')->only(['create', 'store']);
        $this->middleware('permission:Customer documents edit')->only(['edit', 'update']);
        $this->middleware('permission:Customer documents delete')->only(['destroy']);
    }

    public function create(Request $request, User $customer)
    {
        $this->ensureCustomer($customer);

        $type = (string) $request->query('type', CustomerDocument::DELIVERY_NOTE);
        $typeConfig = config('customer_documents.types.' . $type);
        abort_unless($typeConfig, 404);

        return view('customers.documents.form', $this->formData($customer, $type, null));
    }

    public function store(Request $request, User $customer, CustomerDocumentService $service)
    {
        $this->ensureCustomer($customer);

        $data = $this->validated($request);
        $document = $service->create($customer, $data, $request->user());

        return redirect()
            ->route('customers.show', ['customer' => $customer->id, 'tab' => $document->type === CustomerDocument::POD ? 'pods' : 'documents'])
            ->with('success', $document->type_label . ' ' . $document->document_number . ' created.');
    }

    public function show(User $customer, CustomerDocument $document)
    {
        $this->ensureDocument($customer, $document);
        $document->load(['customer', 'trip.vehicle', 'trip.trailer', 'trip.driver', 'driver', 'vehicle', 'trailer', 'creator', 'updater']);

        return view('customers.documents.show', [
            'customer' => $customer,
            'document' => $document,
            'typeConfig' => $document->getTypeConfig(),
        ]);
    }

    public function edit(User $customer, CustomerDocument $document)
    {
        $this->ensureDocument($customer, $document);

        return view('customers.documents.form', $this->formData($customer, $document->type, $document));
    }

    public function update(Request $request, User $customer, CustomerDocument $document, CustomerDocumentService $service)
    {
        $this->ensureDocument($customer, $document);

        $data = $this->validated($request, $document);
        $service->update($document, $data, $request->user());

        return redirect()
            ->route('customers.documents.show', [$customer, $document])
            ->with('success', $document->document_number . ' updated.');
    }

    public function destroy(User $customer, CustomerDocument $document)
    {
        $this->ensureDocument($customer, $document);

        $tab = $document->type === CustomerDocument::POD ? 'pods' : 'documents';
        $number = $document->document_number;
        $document->update(['updated_by' => auth()->id()]);
        $document->delete();

        return redirect()
            ->route('customers.show', ['customer' => $customer->id, 'tab' => $tab])
            ->with('success', $number . ' deleted. The audit trail is kept.');
    }

    public function download(User $customer, CustomerDocument $document, CustomerDocumentService $service)
    {
        $this->ensureDocument($customer, $document);

        if ($document->has_file) {
            return Storage::disk('public')->download(
                $document->file_path,
                $document->file_name ?: basename($document->file_path)
            );
        }

        $pdf = Pdf::loadView('customers.documents.document', [
            'document' => $this->loadedDocument($document),
            'company' => $service->company(),
            'print' => false,
            'imageData' => null,
        ])->setPaper('a4');

        return $pdf->download($document->document_number . '.pdf');
    }

    public function print(User $customer, CustomerDocument $document, CustomerDocumentService $service)
    {
        $this->ensureDocument($customer, $document);

        if ($document->has_file && !$document->is_image) {
            return response()->file(Storage::disk('public')->path($document->file_path), [
                'Content-Type' => $document->mime_type ?: 'application/pdf',
            ]);
        }

        $imageData = null;
        if ($document->is_image) {
            $imageData = 'data:' . $document->mime_type . ';base64,'
                . base64_encode(Storage::disk('public')->get($document->file_path));
        }

        return view('customers.documents.document', [
            'document' => $this->loadedDocument($document),
            'company' => $service->company(),
            'print' => true,
            'imageData' => $imageData,
        ]);
    }

    public function email(Request $request, User $customer, CustomerDocument $document, CustomerDocumentService $service)
    {
        $this->ensureDocument($customer, $document);

        $to = $customer->email;
        if (!$to) {
            return back()->with('error', 'This customer has no email address on file.');
        }

        $document = $this->loadedDocument($document);
        [$data, $name, $mime] = $this->attachment($document, $service);

        try {
            Mail::to($to)->send(new DocumentMail(
                mailSubject: $document->type_label . ' ' . $document->document_number,
                customerName: $customer->name,
                documentLabel: $document->type_label,
                documentNumber: $document->document_number,
                intro: 'Please find your ' . strtolower($document->type_label) . ' attached to this email.',
                attachmentData: $data,
                attachmentName: $name,
                attachmentMime: $mime,
            ));
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'The email could not be sent. Check the mail configuration and try again.');
        }

        return back()->with('success', $document->type_label . ' emailed to ' . $to . '.');
    }

    private function attachment(CustomerDocument $document, CustomerDocumentService $service): array
    {
        if ($document->has_file) {
            return [
                Storage::disk('public')->get($document->file_path),
                $document->file_name ?: basename($document->file_path),
                $document->mime_type ?: 'application/octet-stream',
            ];
        }

        $pdf = Pdf::loadView('customers.documents.document', [
            'document' => $document,
            'company' => $service->company(),
            'print' => false,
            'imageData' => null,
        ])->setPaper('a4');

        return [$pdf->output(), $document->document_number . '.pdf', 'application/pdf'];
    }

    private function formData(User $customer, string $type, ?CustomerDocument $document): array
    {
        $typeConfig = config('customer_documents.types.' . $type);
        abort_unless($typeConfig, 404);

        return [
            'customer' => $customer,
            'document' => $document,
            'type' => $type,
            'typeConfig' => $typeConfig,
            'statuses' => array_intersect_key(
                config('customer_documents.statuses', []),
                array_flip($typeConfig['statuses'] ?? [])
            ),
            'trips' => Trip::with(['vehicle', 'trailer', 'driver', 'originCountry', 'destinationCountry'])
                ->where('customer_id', $customer->id)
                ->orderByDesc('id')
                ->get(),
            'currencies' => Currency::where('is_active', true)->orderBy('code')->get(['code', 'name']),
        ];
    }

    private function validated(Request $request, ?CustomerDocument $document = null): array
    {
        $type = $document?->type ?? (string) $request->input('type');
        $typeConfig = config('customer_documents.types.' . $type);
        abort_unless($typeConfig, 404);

        $statusKeys = $typeConfig['statuses'] ?? [];
        $rules = [
            'trip_id' => [($typeConfig['trip_required'] ?? false) && !$document ? 'required' : 'nullable', 'integer',
                Rule::exists('trips', 'id')->whereNull('deleted_at')],
            'status' => ['nullable', Rule::in($statusKeys)],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'currency_code' => ['nullable', 'string', 'size:3', Rule::exists('currencies', 'code')->where('is_active', true)],
            'exchange_rate' => ['nullable', 'numeric', 'gt:0'],
            'signed_by' => ['nullable', 'string', 'max:150'],
            'signed_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'file' => [
                $type === CustomerDocument::POD && !$document ? 'required' : 'nullable',
                'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp',
            ],
        ];

        $data = $request->validate($rules);
        $data['type'] = $type;

        if (!($typeConfig['has_amount'] ?? false)) {
            unset($data['amount'], $data['currency_code'], $data['exchange_rate']);
        }
        if (!($typeConfig['has_validity'] ?? false)) {
            $data['valid_until'] = null;
        }
        if ($data['amount'] ?? null) {
            $data['currency_code'] = strtoupper((string) ($data['currency_code'] ?? ''));
        }

        return $data;
    }

    private function loadedDocument(CustomerDocument $document): CustomerDocument
    {
        return $document->load([
            'customer', 'trip.vehicle', 'trip.trailer', 'trip.driver',
            'driver', 'vehicle', 'trailer',
        ]);
    }

    private function ensureCustomer(User $customer): void
    {
        abort_unless($customer->user_type === 'C', 404);
    }

    private function ensureDocument(User $customer, CustomerDocument $document): void
    {
        $this->ensureCustomer($customer);
        abort_unless((int) $document->customer_id === (int) $customer->id, 404);
    }
}
