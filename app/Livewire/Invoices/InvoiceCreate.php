<?php

namespace App\Livewire\Invoices;

use Livewire\Component;
use App\Models\Customer;
use App\Domain\Billing\Invoices\Actions\CreateInvoiceAction;
use App\Domain\Billing\Invoices\Data\InvoiceData;
use App\Support\Tenancy\CurrentOrganization;

class InvoiceCreate extends Component
{
    public int|string $customer_id = '';
    public string $issue_date = '';
    public string $due_date = '';
    public string $notes = '';
    public array $lines = [
        ['description' => '', 'quantity' => 1, 'unit_price' => '', 'vat_rate' => 20],
    ];

    public function mount(): void
    {
        $this->issue_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');
    }

    public function addLine(): void
    {
        $this->lines[] = ['description' => '', 'quantity' => 1, 'unit_price' => '', 'vat_rate' => 20];
    }

    public function removeLine(int $index): void
    {
        array_splice($this->lines, $index, 1);
        if (empty($this->lines)) {
            $this->addLine();
        }
    }

    public function getCustomersProperty()
    {
        return Customer::query()
            ->where('organization_id', CurrentOrganization::id())
            ->orderBy('name')
            ->get();
    }

    public function getSubtotalProperty(): float
    {
        $total = 0;
        foreach ($this->lines as $line) {
            $qty = (float) ($line['quantity'] ?? 0);
            $price = (float) ($line['unit_price'] ?? 0);
            $total += $qty * $price;
        }
        return $total;
    }

    public function getTotalWithVatProperty(): float
    {
        $total = 0;
        foreach ($this->lines as $line) {
            $qty = (float) ($line['quantity'] ?? 0);
            $price = (float) ($line['unit_price'] ?? 0);
            $vat = (float) ($line['vat_rate'] ?? 20);
            $lineTotal = $qty * $price;
            $total += $lineTotal + ($lineTotal * $vat / 100);
        }
        return $total;
    }

    protected function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'notes' => 'nullable|string|max:1000',
            'lines' => 'required|array|min:1',
            'lines.*.description' => 'required|string|max:255',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.vat_rate' => 'required|numeric|min:0|max:100',
        ];
    }

    protected $messages = [
        'customer_id.required' => 'Veuillez sélectionner un client.',
        'customer_id.exists' => 'Le client sélectionné est invalide.',
        'issue_date.required' => 'La date d\'émission est obligatoire.',
        'due_date.required' => 'La date d\'échéance est obligatoire.',
        'due_date.after_or_equal' => 'La date d\'échéance doit être après la date d\'émission.',
        'lines.required' => 'Au moins une ligne est requise.',
        'lines.*.description.required' => 'La description est obligatoire.',
        'lines.*.quantity.required' => 'La quantité est obligatoire.',
        'lines.*.unit_price.required' => 'Le prix unitaire est obligatoire.',
        'lines.*.vat_rate.required' => 'Le taux de TVA est obligatoire.',
    ];

    public function save(): void
    {
        $validated = $this->validate();

        // Convert unit_price from euros to centimes
        $lines = array_map(function ($line) {
            $line['unit_price'] = (int) round((float) $line['unit_price'] * 100);
            return $line;
        }, $validated['lines']);

        $data = new InvoiceData(
            customerId: (int) $validated['customer_id'],
            issueDate: $validated['issue_date'],
            dueDate: $validated['due_date'],
            lines: $lines,
            notes: $validated['notes'] ?? null,
        );

        $action = app(CreateInvoiceAction::class);
        $invoice = $action->execute($data, CurrentOrganization::id());

        session()->flash('success', 'Facture ' . $invoice->number . ' créée avec succès!');
        $this->redirect(route('invoices.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.invoices.invoice-create');
    }
}
