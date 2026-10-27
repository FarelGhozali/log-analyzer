<?php

use App\Models\ExpertRule;
use App\Models\User;

it('creates the configured admin account and the default knowledge base', function () {
    $this->seed();

    expect(User::query()->where('email', config('ids.admin.email'))->exists())->toBeTrue()
        ->and(ExpertRule::query()->count())->toBe(5);
});
