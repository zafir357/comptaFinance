<?php

namespace App\Livewire\Invoices;

use Livewire\Component;
use App\Models\Invoice;
use App\Support\Tenancy\CurrentOrganization;

class InvoiceShow extends Component
{
    public Invoice $invoice;

    public function mount(Invoice $invoice): void
    {
        // Security: ensure invoice belongs to current organization
        abort_if(
            $invoice->organization_id !== CurrentOrganization::id(),
            403,
            'Accès refusé.'
        );
        $this->invoice = $invoice->load(['customer', 'lines']);
    }

    public function markAsSent(): void
    {
        $this->invoice->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
        $this->invoice->refresh();
        session()->flash('success', 'Facture marquée comme envoyée.');
    }

    public function markAsPaid(): void
    {
        $this->invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        $this->invoice->refresh();
        session()->flash('success', 'Facture marquée comme payée.');
    }

    public function render()
    {
        return view('livewire.invoices.invoice-show');
    }
}
