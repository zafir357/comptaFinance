<?php

namespace App\Http\Controllers;

use App\Domain\Banking\Actions\ImportBankTransactionsAction;
use App\Domain\Banking\Services\BankTransactionCsvParser;
use Illuminate\Http\Request;

class BankImportController extends Controller
{
    public function index()
    {
        return view('banking.import');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $content = file_get_contents($request->file('csv_file')->getRealPath());

        try {
            $parser = app(BankTransactionCsvParser::class);
            $transactions = $parser->parse($content);
            
            // Store in session for import step
            session(['csv_content' => $content]);

            return view('banking.import', [
                'preview' => array_slice($transactions, 0, 10),
                'total' => count($transactions),
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['csv_file' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    public function import(Request $request)
    {
        $content = session('csv_content');

        if (!$content) {
            return back()->withErrors(['csv_file' => 'Session expirée. Veuillez recharger le fichier.']);
        }

        try {
            $parser = app(BankTransactionCsvParser::class);
            $transactions = $parser->parse($content);

            $action = app(ImportBankTransactionsAction::class);
            $imported = $action->handle($transactions);

            session()->forget('csv_content');

            return redirect()->route('banking.index')
                ->with('success', $imported->count() . ' transactions importées avec succès!');
        } catch (\Exception $e) {
            return back()->withErrors(['csv_file' => 'Erreur: ' . $e->getMessage()]);
        }
    }
}
