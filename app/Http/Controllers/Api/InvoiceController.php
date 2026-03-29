<?php

namespace App\Http\Controllers\Api;

use App\Domain\Billing\Invoices\Actions\CreateInvoiceAction;
use App\Domain\Billing\Invoices\Actions\MarkInvoicePaidAction;
use App\Domain\Billing\Invoices\Actions\UpdateInvoiceAction;
use App\Domain\Billing\Invoices\Data\InvoiceData;
use App\Domain\Billing\Invoices\Repositories\InvoiceRepository;
use App\Http\Requests\Invoices\StoreInvoiceRequest;
use App\Http\Requests\Invoices\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * CONTROLLER: InvoiceController (API)
 *
 * RESTful API endpoints for invoices.
 * All operations scoped to current organization.
 */
class InvoiceController
{
    public function __construct(
        private InvoiceRepository $repository,
        private CreateInvoiceAction $createAction,
        private UpdateInvoiceAction $updateAction,
        private MarkInvoicePaidAction $paidAction,
    ) {}

    /**
     * GET /api/invoices
     * List all invoices (with optional filters).
     */
    public function index(): AnonymousResourceCollection
    {
        $query = $this->repository->query();

        // Filter by status if provided
        if (request('status')) {
            $query->where('status', request('status'));
        }

        // Filter by customer if provided
        if (request('customer_id')) {
            $query->where('customer_id', request('customer_id'));
        }

        $invoices = $query->with('customer')->latest()->paginate(15);

        return InvoiceResource::collection($invoices);
    }

    /**
     * POST /api/invoices
     * Create a new invoice.
     */
    public function store(StoreInvoiceRequest $request): InvoiceResource
    {
        $invoice = $this->createAction->handle(
            InvoiceData::fromArray($request->validated())
        );

        return new InvoiceResource($invoice);
    }

    /**
     * GET /api/invoices/{invoice}
     * Get a specific invoice.
     */
    public function show(Invoice $invoice): InvoiceResource
    {
        $this->authorize('view', $invoice);
        return new InvoiceResource($invoice->load('lines', 'customer'));
    }

    /**
     * PUT /api/invoices/{invoice}
     * Update an invoice.
     */
    public function update(Invoice $invoice, UpdateInvoiceRequest $request): InvoiceResource
    {
        $this->authorize('update', $invoice);

        $invoice = $this->updateAction->handle(
            $invoice,
            InvoiceData::fromArray($request->validated())
        );

        return new InvoiceResource($invoice);
    }

    /**
     * DELETE /api/invoices/{invoice}
     * Delete an invoice.
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        $this->authorize('delete', $invoice);
        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted']);
    }

    /**
     * POST /api/invoices/{invoice}/mark-paid
     * Mark invoice as paid.
     */
    public function markPaid(Invoice $invoice): InvoiceResource
    {
        $this->authorize('update', $invoice);
        $invoice = $this->paidAction->handle($invoice);

        return new InvoiceResource($invoice);
    }
}
