<?php

namespace App\Domain\Billing\Invoices\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * SERVICE: InvoicePdfService
 *
 * Generates a professional PDF from an invoice model.
 * Includes header, customer info, line items, totals, and notes.
 *
 * All amounts are converted from cents to euros for display.
 * Uses Barryvdh DomPDF for rendering.
 *
 * Usage:
 *   $pdfContent = app(InvoicePdfService::class)->handle($invoice);
 *   return response($pdfContent, 200, [
 *       'Content-Type' => 'application/pdf',
 *       'Content-Disposition' => 'attachment; filename="invoice.pdf"',
 *   ]);
 */
class InvoicePdfService
{
    /**
     * Generate PDF content for the given invoice.
     *
     * @param Invoice $invoice The invoice to render
     * @return string Raw PDF content (binary string)
     */
    public function handle(Invoice $invoice): string
    {
        // Load the invoice with relationships
        $invoice->load(['customer', 'lines']);

        // Prepare the HTML content
        $html = $this->renderHtml($invoice);

        // Generate PDF from HTML
        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true);

        return $pdf->output();
    }

    /**
     * Render HTML for the invoice PDF.
     *
     * @param Invoice $invoice
     * @return string HTML content
     */
    private function renderHtml(Invoice $invoice): string
    {
        $statusColor = $this->getStatusColor($invoice->status);
        $statusLabel = ucfirst($invoice->status);

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Invoice {$invoice->number}</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                body {
                    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                    font-size: 11px;
                    color: #333;
                    line-height: 1.6;
                }

                .container {
                    padding: 40px;
                    max-width: 900px;
                    margin: 0 auto;
                }

                .header {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    margin-bottom: 40px;
                    border-bottom: 2px solid #0066cc;
                    padding-bottom: 20px;
                }

                .header-left h1 {
                    font-size: 28px;
                    color: #0066cc;
                    margin-bottom: 8px;
                }

                .header-right {
                    text-align: right;
                }

                .invoice-meta {
                    display: flex;
                    justify-content: space-between;
                    margin-bottom: 30px;
                }

                .meta-block {
                    flex: 1;
                }

                .meta-block h3 {
                    font-size: 12px;
                    font-weight: 700;
                    color: #0066cc;
                    margin-bottom: 8px;
                    text-transform: uppercase;
                }

                .meta-block p {
                    font-size: 11px;
                    margin-bottom: 4px;
                }

                .status-badge {
                    display: inline-block;
                    padding: 6px 12px;
                    border-radius: 4px;
                    font-weight: 600;
                    font-size: 11px;
                    text-transform: uppercase;
                    color: white;
                    margin-bottom: 8px;
                }

                .status-draft {
                    background-color: #999;
                }

                .status-sent {
                    background-color: #0066cc;
                }

                .status-paid {
                    background-color: #28a745;
                }

                .section-title {
                    font-size: 13px;
                    font-weight: 700;
                    color: #0066cc;
                    margin-top: 25px;
                    margin-bottom: 10px;
                    text-transform: uppercase;
                }

                .customer-info {
                    background-color: #f5f5f5;
                    padding: 15px;
                    border-radius: 4px;
                    margin-bottom: 20px;
                }

                .customer-info p {
                    font-size: 11px;
                    margin-bottom: 4px;
                }

                .customer-name {
                    font-weight: 600;
                    font-size: 12px;
                    margin-bottom: 8px;
                }

                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }

                .line-items-table {
                    margin: 25px 0;
                }

                .line-items-table th {
                    background-color: #0066cc;
                    color: white;
                    padding: 12px;
                    text-align: left;
                    font-size: 11px;
                    font-weight: 600;
                }

                .line-items-table td {
                    padding: 10px 12px;
                    border-bottom: 1px solid #ddd;
                    font-size: 11px;
                }

                .line-items-table tbody tr:nth-child(even) {
                    background-color: #f9f9f9;
                }

                .text-right {
                    text-align: right;
                }

                .text-center {
                    text-align: center;
                }

                .totals-section {
                    margin: 30px 0;
                    display: flex;
                    justify-content: flex-end;
                }

                .totals-box {
                    width: 300px;
                }

                .totals-box .row {
                    display: flex;
                    justify-content: space-between;
                    padding: 8px 0;
                    border-bottom: 1px solid #ddd;
                    font-size: 11px;
                }

                .totals-box .row.total {
                    border-bottom: 2px solid #0066cc;
                    font-weight: 700;
                    font-size: 14px;
                    padding: 12px 0;
                    background-color: #f5f5f5;
                }

                .totals-box .row.subtotal,
                .totals-box .row.vat {
                    background-color: #fafafa;
                }

                .notes-section {
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                }

                .notes-title {
                    font-size: 12px;
                    font-weight: 700;
                    color: #0066cc;
                    margin-bottom: 8px;
                    text-transform: uppercase;
                }

                .notes-content {
                    font-size: 10px;
                    line-height: 1.5;
                    color: #555;
                    font-style: italic;
                }

                .footer {
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                    text-align: center;
                    font-size: 9px;
                    color: #999;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <!-- Header -->
                <div class="header">
                    <div class="header-left">
                        <h1>INVOICE</h1>
                    </div>
                    <div class="header-right">
                        <div class="status-badge status-{$invoice->status}">
                            {$statusLabel}
                        </div>
                    </div>
                </div>

                <!-- Invoice Meta -->
                <div class="invoice-meta">
                    <div class="meta-block">
                        <h3>Invoice #</h3>
                        <p>{$invoice->number}</p>
                    </div>
                    <div class="meta-block">
                        <h3>Issue Date</h3>
                        <p>{$invoice->issue_date->format('d/m/Y')}</p>
                    </div>
                    <div class="meta-block">
                        <h3>Due Date</h3>
                        <p>{$invoice->due_date->format('d/m/Y')}</p>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="section-title">Bill To</div>
                <div class="customer-info">
                    <div class="customer-name">{$invoice->customer->name}</div>
                    <p>{$invoice->customer->email}</p>
                    {$this->renderCustomerAddress($invoice->customer)}
                </div>

                <!-- Line Items -->
                <div class="section-title">Items</div>
                <table class="line-items-table">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Description</th>
                            <th style="width: 12%;" class="text-center">Qty</th>
                            <th style="width: 15%;" class="text-right">Unit Price</th>
                            <th style="width: 10%;" class="text-center">VAT %</th>
                            <th style="width: 15%;" class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$this->renderLineItems($invoice)}
                    </tbody>
                </table>

                <!-- Totals -->
                <div class="totals-section">
                    <div class="totals-box">
                        <div class="row subtotal">
                            <span>Subtotal</span>
                            <span>€ {$invoice->subtotal_in_euros}</span>
                        </div>
                        <div class="row vat">
                            <span>VAT</span>
                            <span>€ {$invoice->vat_total_in_euros}</span>
                        </div>
                        <div class="row total">
                            <span>Total</span>
                            <span>€ {$invoice->total_in_euros}</span>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                {$this->renderNotes($invoice)}

                <!-- Footer -->
                <div class="footer">
                    <p>This invoice was generated on {now()->format('d/m/Y H:i')}</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Render customer address in the PDF.
     *
     * @param object $customer
     * @return string HTML snippet
     */
    private function renderCustomerAddress(object $customer): string
    {
        $address = [];

        if ($customer->street ?? null) {
            $address[] = htmlspecialchars($customer->street);
        }
        if ($customer->postal_code ?? null || $customer->city ?? null) {
            $parts = [];
            if ($customer->postal_code ?? null) {
                $parts[] = htmlspecialchars($customer->postal_code);
            }
            if ($customer->city ?? null) {
                $parts[] = htmlspecialchars($customer->city);
            }
            $address[] = implode(' ', $parts);
        }
        if ($customer->country ?? null) {
            $address[] = htmlspecialchars($customer->country);
        }

        if (empty($address)) {
            return '';
        }

        return '<p>' . implode('<br>', $address) . '</p>';
    }

    /**
     * Render line items for the PDF table.
     *
     * @param Invoice $invoice
     * @return string HTML for table rows
     */
    private function renderLineItems(Invoice $invoice): string
    {
        $html = '';

        foreach ($invoice->lines as $line) {
            $totalEuros = number_format($line->total / 100, 2, '.', '');
            $unitPriceEuros = number_format($line->unit_price / 100, 2, '.', '');
            $vatRate = number_format($line->vat_rate, 2, '.', '');
            $quantity = number_format($line->quantity, 2, '.', '');

            $html .= <<<HTML
            <tr>
                <td>{$line->description}</td>
                <td class="text-center">{$quantity}</td>
                <td class="text-right">€ {$unitPriceEuros}</td>
                <td class="text-center">{$vatRate} %</td>
                <td class="text-right">€ {$totalEuros}</td>
            </tr>
            HTML;
        }

        return $html;
    }

    /**
     * Render notes section if present.
     *
     * @param Invoice $invoice
     * @return string HTML for notes section or empty string
     */
    private function renderNotes(Invoice $invoice): string
    {
        if (!$invoice->notes) {
            return '';
        }

        $notes = htmlspecialchars($invoice->notes);

        return <<<HTML
        <div class="notes-section">
            <div class="notes-title">Notes</div>
            <div class="notes-content">{$notes}</div>
        </div>
        HTML;
    }

    /**
     * Get color code for status badge.
     *
     * @param string $status
     * @return string
     */
    private function getStatusColor(string $status): string
    {
        return match ($status) {
            'draft' => '#999',
            'sent' => '#0066cc',
            'paid' => '#28a745',
            default => '#666',
        };
    }
}
