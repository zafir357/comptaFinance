<?php

namespace App\Notifications;

use App\Models\Expense;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReceiptProcessedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Expense $expense
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'receipt_processed',
            'expense_id' => $this->expense->id,
            'expense_title' => $this->expense->title,
            'expense_amount' => $this->expense->total_in_euros,
            'expense_date' => $this->expense->date,
            'message' => "Le reçu pour '{$this->expense->title}' ({$this->expense->total_in_euros}€) a été traité avec succès.",
            'action_url' => route('expenses.show', $this->expense->id),
            'icon' => 'document-check',
            'color' => 'success',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reçu traité - ' . $this->expense->title)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("Le reçu pour la dépense **{$this->expense->title}** a été traité avec succès.")
            ->line("Montant: {$this->expense->total_in_euros}€")
            ->line("Date: {$this->expense->date}")
            ->action('Voir la dépense', route('expenses.show', $this->expense->id))
            ->line('Merci d\'utiliser ComptaFinance!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'expense_id' => $this->expense->id,
            'title' => $this->expense->title,
            'amount' => $this->expense->total_in_euros,
        ];
    }
}
