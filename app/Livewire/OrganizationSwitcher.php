<?php

namespace App\Livewire;

use App\Support\Tenancy\CurrentOrganization;
use Livewire\Component;

class OrganizationSwitcher extends Component
{
    public int $selectedOrgId;

    public function mount()
    {
        $this->selectedOrgId = app(CurrentOrganization::class)->id() ?? 0;
    }

    public function switchOrganization(int $orgId)
    {
        $org = auth()->user()->organizations()->findOrFail($orgId);
        app(CurrentOrganization::class)->set($org);
        $this->selectedOrgId = $orgId;
        $this->redirect(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.organization-switcher', [
            'organizations' => auth()->user()->organizations()->get(),
            'currentOrg' => app(CurrentOrganization::class)->get(),
        ]);
    }
}
