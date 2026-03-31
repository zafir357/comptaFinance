<?php

namespace App\Services;

use App\Domain\Banking\Repositories\BankTransactionRepository;
use App\Domain\Billing\Invoices\Repositories\InvoiceRepository;
use App\Domain\Expenses\Repositories\ExpenseRepository;
use App\Domain\Support\Repositories\TicketRepository;
use App\Models\Customer;
use App\Support\Tenancy\CurrentOrganization;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * SERVICE: DashboardStatsService
 *
 * Calculate key performance indicators (KPIs) for the current organization.
 * Provides aggregated statistics for the dashboard, with smart caching
 * to prevent N+1 queries and repeated database hits.
 *
 * All monetary values are in CENTS (integers).
 * Results are cached for 5 minutes per organization.
 *
 * Usage:
 *   $stats = app(DashboardStatsService::class)->getStats();
 *   echo $stats['totalInvoicedThisMonth']; // in cents
 *   echo $stats['recentInvoices']; // Collection of Invoice models
 */
class DashboardStatsService
{
    private const CACHE_TTL_MINUTES = 5;

    public function __construct(
        private CurrentOrganization $tenancy,
        private InvoiceRepository $invoiceRepository,
        private ExpenseRepository $expenseRepository,
        private BankTransactionRepository $transactionRepository,
        private TicketRepository $ticketRepository,
    ) {}

    /**
     * Get all dashboard statistics for the current organization.
     *
     * Returns array with keys:
     * - totalInvoicedThisMonth (int): Sum of invoiced amounts this month in cents
     * - totalExpensesThisMonth (int): Sum of expense totals this month in cents
     * - outstandingInvoices (int): Sum of unpaid invoice amounts in cents
     * - unreconciledTransactions (int): Count of unreconciled bank transactions
     * - openTickets (int): Count of open support tickets
     * - recentInvoices (Collection): 5 most recent invoices with eager-loaded relations
     * - topCustomers (Collection): Top customers by invoice count
     *
     * @return array
     * @throws \Exception If no organization is currently set
     */
    public function getStats(): array
    {
        $orgId = $this->tenancy->id();

        if (!$orgId) {
            throw new \Exception('No organization is currently set in session.');
        }

        $cacheKey = "dashboard_stats:{$orgId}";

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () {
            return [
                'totalInvoicedThisMonth' => $this->getTotalInvoicedThisMonth(),
                'totalExpensesThisMonth' => $this->getTotalExpensesThisMonth(),
                'outstandingInvoices' => $this->getOutstandingInvoices(),
                'unreconciledTransactions' => $this->getUnreconciledTransactions(),
                'openTickets' => $this->getOpenTickets(),
                'recentInvoices' => $this->getRecentInvoices(),
                'topCustomers' => $this->getTopCustomers(),
            ];
        });
    }

    /**
     * Flush the cached dashboard statistics for the current organization.
     * Call this after any invoice, expense, or transaction updates.
     *
     * @return void
     */
    public function flush(): void
    {
        $orgId = $this->tenancy->id();

        if ($orgId) {
            Cache::forget("dashboard_stats:{$orgId}");
        }
    }

    /**
     * Get total invoiced amount for the current month.
     * Only includes invoices with status 'sent' or 'paid'.
     *
     * @return int Amount in cents
     */
    private function getTotalInvoicedThisMonth(): int
    {
        $sent = $this->invoiceRepository->query()
            ->where('status', 'sent')
            ->whereYear('issue_date', now()->year)
            ->whereMonth('issue_date', now()->month)
            ->sum('total');

        $paid = $this->invoiceRepository->query()
            ->where('status', 'paid')
            ->whereYear('issue_date', now()->year)
            ->whereMonth('issue_date', now()->month)
            ->sum('total');

        return (int) ($sent + $paid);
    }

    /**
     * Get total expenses for the current month.
     *
     * @return int Amount in cents (amount + vat_amount)
     */
    private function getTotalExpensesThisMonth(): int
    {
        return (int) $this->expenseRepository->query()
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->selectRaw('SUM(amount + vat_amount) as total')
            ->value('total') ?? 0;
    }

    /**
     * Get sum of all outstanding (unpaid) invoices.
     *
     * @return int Amount in cents
     */
    private function getOutstandingInvoices(): int
    {
        // Only invoices with status 'sent' (not yet paid)
        return (int) $this->invoiceRepository->query()
            ->where('status', 'sent')
            ->sum('total');
    }

    /**
     * Get count of unreconciled bank transactions.
     *
     * @return int
     */
    private function getUnreconciledTransactions(): int
    {
        return (int) $this->transactionRepository->unreconciled()
            ->count();
    }

    /**
     * Get count of open support tickets.
     *
     * @return int
     */
    private function getOpenTickets(): int
    {
        return (int) $this->ticketRepository->query()
            ->where('status', 'open')
            ->count();
    }

    /**
     * Get 5 most recent invoices with eager-loaded relations.
     * Ordered by issue_date descending.
     *
     * @return Collection
     */
    private function getRecentInvoices(): Collection
    {
        return $this->invoiceRepository->recent(5)->get();
    }

    /**
     * Get top customers by invoice count.
     * Returns up to 5 customers with most invoices.
     *
     * @return Collection Customers with 'invoice_count' attribute
     */
    private function getTopCustomers(): Collection
    {
        return Customer::where('organization_id', $this->tenancy->id())
            ->withCount('invoices')
            ->orderByDesc('invoices_count')
            ->limit(5)
            ->get();
    }
}
