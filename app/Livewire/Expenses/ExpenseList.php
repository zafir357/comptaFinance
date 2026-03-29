<?php

namespace App\Livewire\Expenses;

use App\Models\Expense;
use App\Support\Tenancy\CurrentOrganization;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $categoryFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function getExpensesProperty()
    {
        return Expense::query()
            ->where('organization_id', CurrentOrganization::id())
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('supplier', 'like', '%'.$this->search.'%')
                    ->orWhere('category', 'like', '%'.$this->search.'%');
            }))
            ->when($this->categoryFilter, fn ($q) => $q->where('category', $this->categoryFilter))
            ->latest('date')
            ->paginate(15);
    }

    public function getCategoriesProperty(): array
    {
        return Expense::query()
            ->where('organization_id', CurrentOrganization::id())
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
    }

    public function render()
    {
        return view('livewire.expenses.expense-list');
    }
}
