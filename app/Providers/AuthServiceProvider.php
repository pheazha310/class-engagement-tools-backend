<?php

namespace App\Providers;

use App\Models\Poll;
use App\Models\Wheel;
use App\Policies\PollPolicy;
use App\Policies\WheelPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Poll::class, PollPolicy::class);
        Gate::policy(Wheel::class, WheelPolicy::class);
    }
}
