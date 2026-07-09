<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DistrictResource;
use App\Services\LocationService;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    public function __construct(
        private readonly LocationService $locationService,
    ) {}

    public function index(Request $request)
    {
        $request->validate(['province_id' => 'required|exists:provinces,id']);

        return DistrictResource::collection(
            $this->locationService->getDistricts($request->integer('province_id')),
        );
    }
}
