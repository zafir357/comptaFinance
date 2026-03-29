<?php

namespace App\Livewire;

use App\Services\DashboardStatsService;
use App\Support\Tenancy\CurrentOrganization;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $tenancy = app(CurrentOrganization::class);

        // Guard: no org in session (e.g. fresh login with no memberships)
        if (! $tenancy->isSet()) {
            return redirect()->route('home');
        }

        $stats = app(DashboardStatsService::class)->getStats();

        return view('livewire.dashboard', [
            'stats' => $stats,
            'currentOrg' => $tenancy->get(),
        ]);
    }
}
