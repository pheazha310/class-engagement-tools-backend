<?php

namespace App\Repositories\Contracts;

use App\Models\Country;
use Illuminate\Database\Eloquent\Collection;

interface CountryRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?Country;
}
