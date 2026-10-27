<?php

use App\Models\ExpertRule;
use App\Models\ScanResult;
use App\Models\User;

it('redirects guests to the admin login page', function () {
    $this->get('/admin/log-analyzer')->assertRedirect('/admin/login');
});

it('allows an authenticated user to open the analyzer page', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/log-analyzer')
        ->assertOk();
});

it('renders the dashboard with scan statistics and the latest results', function () {
    $rule = ExpertRule::factory()->create();
    ScanResult::factory()->attack($rule)->create();

    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk();
});
