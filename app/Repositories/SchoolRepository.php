<?php

namespace App\Repositories;

use App\Models\School;
use App\Repositories\Contracts\SchoolRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class SchoolRepository implements SchoolRepositoryInterface
{
    public function getByDistrict(int $districtId): Collection
    {
        return School::where('district_id', $districtId)
            ->orderBy('name')
            ->get();
    }

    public function searchByName(string $query, ?int $districtId = null): Collection
    {
        $q = School::where('name', 'like', "%{$query}%");

        if ($districtId !== null) {
            $q->where('district_id', $districtId);
        }

        return $q->orderBy('name')->get();
    }

    public function find(int $id): ?School
    {
        return School::find($id);
    }

    public function create(array $data): School
    {
        return School::create($data);
    }
}
