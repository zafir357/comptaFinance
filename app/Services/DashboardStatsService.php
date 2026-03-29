<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Support\Tenancy\CurrentOrganization;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($orgId) {
            return [
                'totalInvoicedThisMonth' => $this->getTotalInvoicedThisMonth($orgId),
                'totalExpensesThisMonth' => $this->getTotalExpensesThisMonth($orgId),
                'outstandingInvoices' => $this->getOutstandingInvoices($orgId),
                'unreconciledTransactions' => $this->getUnreconciledTransactions($orgId),
                'openTickets' => $this->getOpenTickets($orgId),
                'recentInvoices' => $this->getRecentInvoices($orgId),
                'topCustomers' => $this->getTopCustomers($orgId),
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
     * @param int $orgId
     * @return int Amount in cents
     */
    private function getTotalInvoicedThisMonth(int $orgId): int
    {
        return (int) Invoice::where('organization_id', $orgId)
            ->whereIn('status', ['sent', 'paid'])
            ->whereYear('issue_date', now()->year)
            ->whereMonth('issue_date', now()->month)
            ->sum('total');
    }

    /**
     * Get total expenses for the current month.
     *
     * @param int $orgId
     * @return int Amount in cents (amount + vat_amount)
     */
    private function getTotalExpensesThisMonth(int $orgId): int
    {
        return (int) \DB::table('expenses')
            ->where('organization_id', $orgId)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->selectRaw('SUM(amount + vat_amount) as total')
            ->value('total') ?? 0;
    }

    /**
     * Get sum of all outstanding (unpaid) invoices.
     *
     * @param int $orgId
     * @return int Amount in cents
     */
    private function getOutstandingInvoices(int $orgId): int
    {
        // Only invoices with status 'sent' (not yet paid)
        return (int) Invoice::where('organization_id', $orgId)
            ->where('status', 'sent')
            ->sum('total');
    }

    /**
     * Get count of unreconciled bank transactions.
     *
     * @param int $orgId
     * @return int
     */
    private function getUnreconciledTransactions(int $orgId): int
    {
        return (int) \DB::table('bank_transactions')
            ->where('organization_id', $orgId)
            ->where('reconciled', false)
            ->count();
    }

    /**
     * Get count of open support tickets.
     *
     * @param int $orgId
     * @return int
     */
    private function getOpenTickets(int $orgId): int
    {
        return (int) \DB::table('tickets')
            ->where('organization_id', $orgId)
            ->where('status', 'open')
            ->count();
    }

    /**
     * Get 5 most recent invoices with eager-loaded relations.
     * Ordered by issue_date descending.
     *
     * @param int $orgId
     * @return Collection
     */
    private function getRecentInvoices(int $orgId): Collection
    {
        return Invoice::where('organization_id', $orgId)
            ->with(['customer', 'lines'])
            ->orderBy('issue_date', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get top customers by invoice count.
     * Returns up to 5 customers with most invoices.
     *
     * @param int $orgId
     * @return Collection Customers with 'invoice_count' attribute
     */
    private function getTopCustomers(int $orgId): Collection
    {
        return Customer::where('organization_id', $orgId)
            ->withCount('invoices')
            ->orderByDesc('invoices_count')
            ->limit(5)
            ->get();
    }
}
