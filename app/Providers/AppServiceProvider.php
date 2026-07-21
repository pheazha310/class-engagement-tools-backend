<?php

namespace App\Providers;

use App\Repositories\Contracts\CountryRepositoryInterface;
use App\Repositories\Contracts\DistrictRepositoryInterface;
use App\Repositories\Contracts\GameHistoryRepositoryInterface;
use App\Repositories\Contracts\PollRepositoryInterface;
use App\Repositories\Contracts\ProvinceRepositoryInterface;
use App\Repositories\Contracts\SchoolRequestRepositoryInterface;
use App\Repositories\Contracts\VoteRepositoryInterface;
use App\Repositories\CountryRepository;
use App\Repositories\DistrictRepository;
use App\Repositories\GameHistoryRepository;
use App\Repositories\PollRepository;
use App\Repositories\ProvinceRepository;
use App\Repositories\SchoolRequestRepository;
use App\Repositories\VoteRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PollRepositoryInterface::class, PollRepository::class);
        $this->app->bind(VoteRepositoryInterface::class, VoteRepository::class);
        $this->app->bind(CountryRepositoryInterface::class, CountryRepository::class);
        $this->app->bind(ProvinceRepositoryInterface::class, ProvinceRepository::class);
        $this->app->bind(DistrictRepositoryInterface::class, DistrictRepository::class);
        $this->app->bind(SchoolRequestRepositoryInterface::class, SchoolRequestRepository::class);
        $this->app->bind(GameHistoryRepositoryInterface::class, GameHistoryRepository::class);
    }

    public function boot(): void
    {
        $this->configureDefaults();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
