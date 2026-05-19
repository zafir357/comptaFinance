<?php

use App\Models\Organization;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users with no org are redirected from dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertRedirect('/');
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $org = Organization::create(['name' => 'Test Org']);
    $org->users()->attach($user, ['role' => 'owner']);
    $this->actingAs($user);
    session(['current_organization_id' => $org->id]);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});