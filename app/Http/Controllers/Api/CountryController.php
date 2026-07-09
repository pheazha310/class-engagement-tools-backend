<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Services\LocationService;

class CountryController extends Controller
{
    public function __construct(
        private readonly LocationService $locationService,
    ) {}

    public function index()
    {
        return CountryResource::collection($this->locationService->getCountries());
    }
}
