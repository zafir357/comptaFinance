<?php

namespace App\Livewire;

use App\Services\DashboardStatsService;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $stats = app(DashboardStatsService::class)->getStats();

        return view('livewire.dashboard', [
            'stats' => $stats,
            'currentOrg' => app(\App\Support\Tenancy\CurrentOrganization::class)->get(),
        ]);
    }
}
