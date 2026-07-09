<?php

namespace App\Repositories\Contracts;

use App\Models\District;
use Illuminate\Database\Eloquent\Collection;

interface DistrictRepositoryInterface
{
    public function getByProvince(int $provinceId): Collection;

    public function find(int $id): ?District;
}
