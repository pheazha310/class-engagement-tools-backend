<?php

namespace App\Repositories\Contracts;

use App\Models\Province;
use Illuminate\Database\Eloquent\Collection;

interface ProvinceRepositoryInterface
{
    public function getByCountry(int $countryId): Collection;

    public function find(int $id): ?Province;
}
