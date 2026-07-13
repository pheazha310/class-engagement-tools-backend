<?php

namespace App\Providers;

use App\Models\Wheel;
use App\Policies\WheelPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Wheel::class, WheelPolicy::class);
    }
}
