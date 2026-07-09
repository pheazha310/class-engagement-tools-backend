<?php

namespace App\Repositories\Contracts;

use App\Models\School;
use Illuminate\Database\Eloquent\Collection;

interface SchoolRepositoryInterface
{
    public function getByDistrict(int $districtId): Collection;

    public function searchByName(string $query, ?int $districtId = null): Collection;

    public function find(int $id): ?School;

    public function create(array $data): School;
}
