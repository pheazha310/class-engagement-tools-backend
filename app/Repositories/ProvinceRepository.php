<?php

namespace App\Repositories;

use App\Models\Province;
use App\Repositories\Contracts\ProvinceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProvinceRepository implements ProvinceRepositoryInterface
{
    public function getByCountry(int $countryId): Collection
    {
        return Province::where('country_id', $countryId)
            ->orderBy('name')
            ->get();
    }

    public function find(int $id): ?Province
    {
        return Province::find($id);
    }
}
