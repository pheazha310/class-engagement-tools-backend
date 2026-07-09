<?php

namespace App\Services;

use App\Repositories\Contracts\CountryRepositoryInterface;
use App\Repositories\Contracts\DistrictRepositoryInterface;
use App\Repositories\Contracts\ProvinceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LocationService
{
    public function __construct(
        private readonly CountryRepositoryInterface $countries,
        private readonly ProvinceRepositoryInterface $provinces,
        private readonly DistrictRepositoryInterface $districts,
    ) {}

    public function getCountries(): Collection
    {
        return $this->countries->all();
    }

    public function getProvinces(int $countryId): Collection
    {
        return $this->provinces->getByCountry($countryId);
    }

    public function getDistricts(int $provinceId): Collection
    {
        return $this->districts->getByProvince($provinceId);
    }
}
