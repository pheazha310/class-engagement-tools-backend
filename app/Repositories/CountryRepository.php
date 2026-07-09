<?php

namespace App\Repositories;

use App\Models\Country;
use App\Repositories\Contracts\CountryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CountryRepository implements CountryRepositoryInterface
{
    public function all(): Collection
    {
        return Country::orderBy('name')->get();
    }

    public function find(int $id): ?Country
    {
        return Country::find($id);
    }
}
