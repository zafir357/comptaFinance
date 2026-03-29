<?php

use App\Models\User;
use App\Models\Organization;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        // Create a default organization for the new user
        $orgName = 'Cabinet de ' . explode(' ', $user->name)[0];
        $organization = Organization::create([
            'name' => $orgName,
            'slug' => Str::slug($orgName) . '-' . Str::random(6),
            'settings' => [],
        ]);

        // Add user as owner of the organization
        $organization->users()->attach($user->id, ['role' => 'owner']);

        // Set the organization in session
        session(['current_organization_id' => $organization->id]);

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header title="Créer un compte" description="Inscrivez-vous pour gérer votre comptabilité" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <div class="grid gap-2">
            <flux:input wire:model="name" id="name" label="Nom complet" type="text" name="name" required autofocus autocomplete="name" placeholder="Jean Dupont" />
        </div>

        <!-- Email Address -->
        <div class="grid gap-2">
            <flux:input wire:model="email" id="email" label="Adresse email" type="email" name="email" required autocomplete="email" placeholder="email@exemple.fr" />
        </div>

        <!-- Password -->
        <div class="grid gap-2">
            <flux:input
                wire:model="password"
                id="password"
                label="Mot de passe"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Mot de passe"
            />
        </div>

        <!-- Confirm Password -->
        <div class="grid gap-2">
            <flux:input
                wire:model="password_confirmation"
                id="password_confirmation"
                label="Confirmer le mot de passe"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Confirmer le mot de passe"
            />
        </div>

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                Créer mon compte
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 text-center text-sm text-zinc-600 dark:text-zinc-400">
        Déjà inscrit ?
        <x-text-link href="{{ route('login') }}">Se connecter</x-text-link>
    </div>
</div>
