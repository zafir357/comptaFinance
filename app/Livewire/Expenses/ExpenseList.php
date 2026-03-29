<?php

namespace App\Livewire\Expenses;

use App\Models\Expense;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $category = '';
    public string $status = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Expense::query();

        if ($this->search) {
            $query->where('supplier', 'like', "%{$this->search}%");
        }

        if ($this->category) {
            $query->where('category', $this->category);
        }

        if ($this->status) {
            $query->where('receipt_status', $this->status);
        }

        $expenses = $query->latest()->paginate(15);

        return view('livewire.expenses.list', [
            'expenses' => $expenses,
        ]);
    }
}
