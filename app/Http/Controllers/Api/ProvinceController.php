<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProvinceResource;
use App\Services\LocationService;
use Illuminate\Http\Request;

class ProvinceController extends Controller
{
    public function __construct(
        private readonly LocationService $locationService,
    ) {}

    public function index(Request $request)
    {
        $request->validate(['country_id' => 'required|exists:countries,id']);

        return ProvinceResource::collection(
            $this->locationService->getProvinces($request->integer('country_id')),
        );
    }
}
