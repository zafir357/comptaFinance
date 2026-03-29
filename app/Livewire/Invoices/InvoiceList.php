<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class InvoiceList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    public function render()
    {
        $query = Invoice::query();

        // Search by invoice number or customer name
        if ($this->search) {
            $query->where('number', 'like', "%{$this->search}%")
                ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$this->search}%"));
        }

        // Filter by status
        if ($this->status) {
            $query->where('status', $this->status);
        }

        $invoices = $query->with('customer')
            ->latest('created_at')
            ->paginate(15);

        return view('livewire.invoices.list', [
            'invoices' => $invoices,
        ]);
    }

    public function deleteInvoice(Invoice $invoice)
    {
        $this->authorize('delete', $invoice);
        $invoice->delete();

        session()->flash('success', 'Invoice deleted');
    }
}
