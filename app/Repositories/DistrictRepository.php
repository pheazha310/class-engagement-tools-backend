<?php

namespace App\Repositories;

use App\Models\District;
use App\Repositories\Contracts\DistrictRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DistrictRepository implements DistrictRepositoryInterface
{
    public function getByProvince(int $provinceId): Collection
    {
        return District::where('province_id', $provinceId)
            ->orderBy('name')
            ->get();
    }

    public function find(int $id): ?District
    {
        return District::find($id);
    }
}
