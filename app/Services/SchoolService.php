<?php

namespace App\Services;

use App\Models\School;
use App\Repositories\Contracts\SchoolRepositoryInterface;
use App\Repositories\Contracts\SchoolRequestRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SchoolService
{
    public function __construct(
        private readonly SchoolRepositoryInterface $schools,
        private readonly SchoolRequestRepositoryInterface $schoolRequests,
    ) {}

    public function getByDistrict(int $districtId): Collection
    {
        return $this->schools->getByDistrict($districtId);
    }

    public function getByProvince(int $provinceId): Collection
    {
        return $this->schools->getByProvince($provinceId);
    }

    public function search(string $query, ?int $districtId = null, ?int $provinceId = null): Collection
    {
        return $this->schools->searchByName($query, $districtId, $provinceId);
    }

    public function requestNewSchool(array $data): School
    {
        return $this->schoolRequests->create($data);
    }
}
