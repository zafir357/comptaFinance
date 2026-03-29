<?php

namespace App\Livewire\Invoices;

use App\Domain\Billing\Invoices\Actions\CreateInvoiceAction;
use App\Domain\Billing\Invoices\Actions\UpdateInvoiceAction;
use App\Domain\Billing\Invoices\Data\InvoiceData;
use App\Models\Customer;
use App\Models\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class InvoiceEditor extends Component
{
    public ?Invoice $invoice = null;

    public int $customer_id = 0;
    public string $issue_date = '';
    public string $due_date = '';
    public string $notes = '';

    public array $lines = [];

    public function mount(?Invoice $invoice = null)
    {
        if ($invoice) {
            $this->invoice = $invoice;
            $this->customer_id = $invoice->customer_id;
            $this->issue_date = $invoice->issue_date->format('Y-m-d');
            $this->due_date = $invoice->due_date->format('Y-m-d');
            $this->notes = $invoice->notes ?? '';

            $this->lines = $invoice->lines->map(fn ($line) => [
                'id' => $line->id,
                'description' => $line->description,
                'quantity' => $line->quantity / 100,
                'unit_price' => $line->unit_price / 100,
                'vat_rate' => $line->vat_rate,
            ])->toArray();
        } else {
            // Add empty line
            $this->addLine();
        }
    }

    public function addLine()
    {
        $this->lines[] = [
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'vat_rate' => 2000, // 20% by default
        ];
    }

    public function removeLine(int $index)
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function calculateTotals(): array
    {
        $subtotal = 0;
        $vat_total = 0;

        foreach ($this->lines as $line) {
            $quantity = (int) ($line['quantity'] * 100);
            $unit_price = (int) ($line['unit_price'] * 100);
            $vat_rate = $line['vat_rate'];

            $line_total = $quantity * $unit_price;
            $vat_on_line = (int) ($line_total * $vat_rate / 10000);

            $subtotal += $line_total;
            $vat_total += $vat_on_line;
        }

        return [
            'subtotal' => $subtotal / 100,
            'vat_total' => $vat_total / 100,
            'total' => ($subtotal + $vat_total) / 100,
        ];
    }

    public function save()
    {
        // Basic validation before hitting the action
        if (! $this->customer_id) {
            $this->addError('customer_id', 'Veuillez sélectionner un client.');
            return;
        }

        if (empty($this->lines)) {
            $this->addError('lines', 'Veuillez ajouter au moins une ligne.');
            return;
        }

        foreach ($this->lines as $i => $line) {
            if (empty(trim($line['description']))) {
                $this->addError("lines.{$i}.description", 'Description requise.');
                return;
            }
        }

        $data = [
            'customer_id' => $this->customer_id,
            'issue_date' => $this->issue_date,
            'due_date' => $this->due_date,
            'notes' => $this->notes,
            'lines' => $this->lines,
        ];

        if ($this->invoice) {
            app(UpdateInvoiceAction::class)->handle(
                $this->invoice,
                InvoiceData::fromArray($data)
            );
            session()->flash('success', 'Facture mise à jour');
        } else {
            app(CreateInvoiceAction::class)->handle(
                InvoiceData::fromArray($data)
            );
            session()->flash('success', 'Facture créée');
        }

        return redirect()->route('invoices.index');
    }

    public function render()
    {
        return view('livewire.invoices.editor', [
            'customers' => Customer::all(),
            'totals' => $this->calculateTotals(),
        ]);
    }
}
