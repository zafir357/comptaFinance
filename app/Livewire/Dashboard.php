<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Support\Tenancy\CurrentOrganization;
use Livewire\Component;

class Dashboard extends Component
{
    public function getStatsProperty(): array
    {
        $orgId = CurrentOrganization::id();

        if (! $orgId) {
            return [
                'total_invoiced' => 0,
                'total_paid' => 0,
                'total_pending' => 0,
                'total_expenses' => 0,
                'invoices_count' => 0,
                'customers_count' => 0,
                'overdue_count' => 0,
                'expenses_count' => 0,
            ];
        }

        $invoices = Invoice::where('organization_id', $orgId);
        $expenses = Expense::where('organization_id', $orgId);

        $totalInvoiced = (clone $invoices)->sum('total');
        $totalPaid = (clone $invoices)->where('status', 'paid')->sum('total');
        $totalPending = (clone $invoices)->whereIn('status', ['draft', 'sent'])->sum('total');
        $overdueCount = (clone $invoices)->where('status', 'overdue')
            ->orWhere(function ($q) use ($orgId) {
                $q->where('organization_id', $orgId)
                    ->where('status', 'sent')
                    ->where('due_date', '<', now());
            })->count();

        return [
            'total_invoiced' => $totalInvoiced,
            'total_paid' => $totalPaid,
            'total_pending' => $totalPending,
            'total_expenses' => $expenses->sum('amount'),
            'invoices_count' => (clone $invoices)->count(),
            'customers_count' => Customer::where('organization_id', $orgId)->count(),
            'overdue_count' => $overdueCount,
            'expenses_count' => Expense::where('organization_id', $orgId)->count(),
        ];
    }

    public function getRecentInvoicesProperty()
    {
        $orgId = CurrentOrganization::id();
        if (! $orgId) {
            return collect();
        }

        return Invoice::with('customer')
            ->where('organization_id', $orgId)
            ->latest()
            ->limit(5)
            ->get();
    }

    public function getRecentExpensesProperty()
    {
        $orgId = CurrentOrganization::id();
        if (! $orgId) {
            return collect();
        }

        return Expense::where('organization_id', $orgId)
            ->latest('date')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
