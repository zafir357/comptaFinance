<?php

namespace App\Domain\Banking\Services;

use App\Domain\Banking\Data\BankTransactionData;
use Carbon\Carbon;

/**
 * SERVICE: BankTransactionCsvParser
 *
 * Parses bank CSV files and converts them to BankTransactionData DTOs.
 *
 * CSV Format: date,description,amount,external_id
 * - date: YYYY-MM-DD format
 * - description: any string (transaction label)
 * - amount: numeric with optional comma (e.g., 1500.50 or 1500,50)
 *           negative for debits, positive for credits
 * - external_id: unique identifier from bank (for idempotency)
 *
 * Usage:
 *   $parser = new BankTransactionCsvParser();
 *   $transactions = $parser->parse($csvContent);
 */
class BankTransactionCsvParser
{
    /**
     * Parse CSV content into BankTransactionData array
     *
     * @param string $csv CSV content as string
     * @return array<int, BankTransactionData>
     * @throws \InvalidArgumentException if CSV is invalid
     */
    public function parse(string $csv): array
    {
        $lines = array_filter(
            explode("\n", trim($csv)),
            fn ($line) => !empty(trim($line))
        );

        if (count($lines) < 1) {
            throw new \InvalidArgumentException('CSV file is empty');
        }

        // Skip header if present (detect by checking first line format)
        $startLine = 0;
        if ($this->isHeaderLine($lines[0])) {
            $startLine = 1;
        }

        $transactions = [];
        $externalIds = [];

        foreach (array_slice($lines, $startLine) as $lineNum => $line) {
            $transaction = $this->parseLine($line, $lineNum + $startLine + 1);

            // Validate uniqueness of external_id within this import
            if (in_array($transaction->external_id, $externalIds)) {
                throw new \InvalidArgumentException(
                    "Duplicate external_id '{$transaction->external_id}' in CSV at line " . ($lineNum + $startLine + 2)
                );
            }

            $externalIds[] = $transaction->external_id;
            $transactions[] = $transaction;
        }

        return $transactions;
    }

    /**
     * Parse a single CSV line into BankTransactionData
     *
     * @param string $line CSV line
     * @param int $lineNumber for error reporting
     * @return BankTransactionData
     * @throws \InvalidArgumentException
     */
    private function parseLine(string $line, int $lineNumber): BankTransactionData
    {
        $parts = str_getcsv($line);

        if (count($parts) < 4) {
            throw new \InvalidArgumentException(
                "CSV line {$lineNumber} has insufficient columns. Expected: date,description,amount,external_id"
            );
        }

        $date = trim($parts[0]);
        $description = trim($parts[1]);
        $amountStr = trim($parts[2]);
        $externalId = trim($parts[3]);

        // Validate date
        try {
            $parsedDate = Carbon::createFromFormat('Y-m-d', $date);
            if (!$parsedDate) {
                throw new \Exception('Invalid date');
            }
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(
                "CSV line {$lineNumber}: Invalid date format '{$date}'. Expected YYYY-MM-DD"
            );
        }

        // Validate description
        if (empty($description) || strlen($description) > 255) {
            throw new \InvalidArgumentException(
                "CSV line {$lineNumber}: Description must be non-empty and less than 255 characters"
            );
        }

        // Validate and convert amount to cents
        if (!$this->isValidAmount($amountStr)) {
            throw new \InvalidArgumentException(
                "CSV line {$lineNumber}: Invalid amount '{$amountStr}'. Must be numeric (e.g., 1500.50)"
            );
        }
        $amountInCents = BankTransactionData::amountInCents($amountStr);

        // Validate external_id
        if (empty($externalId) || strlen($externalId) > 255) {
            throw new \InvalidArgumentException(
                "CSV line {$lineNumber}: external_id must be non-empty and less than 255 characters"
            );
        }

        return new BankTransactionData(
            date: $parsedDate,
            description: $description,
            amount: $amountInCents,
            currency: 'EUR',
            external_id: $externalId,
        );
    }

    /**
     * Check if a line is the CSV header
     */
    private function isHeaderLine(string $line): bool
    {
        $parts = str_getcsv($line);
        $header = array_map('strtolower', array_map('trim', $parts));

        return count($parts) >= 4
            && in_array('date', $header)
            && in_array('description', $header)
            && in_array('amount', $header)
            && in_array('external_id', $header);
    }

    /**
     * Validate amount format
     */
    private function isValidAmount(string $amount): bool
    {
        // Allow: -1234, 1234.50, 1234,50, -1234.50, -1234,50
        return (bool) preg_match('/^-?\d+([.,]\d{1,2})?$/', $amount);
    }
}
