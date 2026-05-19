<?php

namespace App\Livewire;

use App\Services\DashboardStatsService;
use App\Support\Tenancy\CurrentOrganization;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public function mount(): void
    {
        if (! app(CurrentOrganization::class)->isSet()) {
            $this->redirect(route('home'));
        }
    }

    public function render()
    {
        $tenancy = app(CurrentOrganization::class);

        $stats = $tenancy->isSet()
            ? app(DashboardStatsService::class)->getStats()
            : [];

        return view('livewire.dashboard', [
            'stats' => $stats,
            'currentOrg' => $tenancy->get(),
        ]);
    }
}
