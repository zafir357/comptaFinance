<?php

namespace App\Livewire\Expenses;

use App\Domain\Expenses\Actions\CreateExpenseAction;
use App\Domain\Expenses\Data\ExpenseData;
use App\Models\Expense;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class ExpenseCreate extends Component
{
    use WithFileUploads;

    public string $category = '';
    public string $supplier = '';
    public string $amount = '';
    public string $vat_amount = '';
    public string $date = '';
    public $receipt = null;
    public bool $processing = false;

    protected $rules = [
        'category' => 'required|in:travel,meals,supplies,utilities,other',
        'supplier' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0',
        'vat_amount' => 'required|numeric|min:0',
        'date' => 'required|date',
        'receipt' => 'nullable|file|max:5120',
    ];

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function create()
    {
        $this->validate();

        $receiptPath = null;
        if ($this->receipt) {
            $receiptPath = $this->receipt->store('expenses', 'private');
        }

        // Create DTO from form data using fromArray()
        $data = ExpenseData::fromArray([
            'category' => $this->category,
            'supplier' => $this->supplier,
            'amount' => $this->amount,
            'vat_amount' => $this->vat_amount,
            'date' => $this->date,
            'receipt_path' => $receiptPath,
        ]);

        app(CreateExpenseAction::class)->handle($data);

        session()->flash('success', 'Note de frais créée avec succès!');
        return redirect()->route('expenses.index');
    }

    public function render()
    {
        return view('livewire.expenses.create');
    }
}
