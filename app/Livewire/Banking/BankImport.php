<?php

namespace App\Livewire\Banking;

use App\Domain\Banking\Actions\ImportBankTransactionsAction;
use App\Domain\Banking\Services\BankTransactionCsvParser;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class BankImport extends Component
{
    use WithFileUploads;

    public $csvFile = null;
    public array $preview = [];
    public bool $showPreview = false;
    public bool $importing = false;

    public function preview()
    {
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt',
        ]);

        if (!$this->csvFile) {
            $this->addError('csvFile', 'Veuillez sélectionner un fichier CSV');
            return;
        }

        $content = file_get_contents($this->csvFile->getRealPath());

        try {
            $transactions = app(BankTransactionCsvParser::class)->parse($content);
            $this->preview = array_slice($transactions, 0, 5); // Show first 5
            $this->showPreview = true;
        } catch (\Exception $e) {
            $this->addError('csvFile', 'Erreur lors du parsing: ' . $e->getMessage());
        }
    }

    public function import()
    {
        if (!$this->csvFile) {
            $this->addError('csvFile', 'Sélectionner un fichier CSV');
            return;
        }

        $this->importing = true;

        try {
            $content = file_get_contents($this->csvFile->getRealPath());
            $transactions = app(BankTransactionCsvParser::class)->parse($content);

            $imported = app(ImportBankTransactionsAction::class)->handle($transactions);

            session()->flash('success', count($imported) . ' transactions importées avec succès!');
            $this->csvFile = null;
            $this->preview = [];
            $this->showPreview = false;

            return redirect()->route('banking.index');
        } catch (\Exception $e) {
            $this->addError('csvFile', 'Erreur: ' . $e->getMessage());
        } finally {
            $this->importing = false;
        }
    }

    public function render()
    {
        return view('livewire.banking.import');
    }
}
