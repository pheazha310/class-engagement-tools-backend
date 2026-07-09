<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SchoolResource;
use App\Services\SchoolService;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function __construct(
        private readonly SchoolService $schoolService,
    ) {}

    public function index(Request $request)
    {
        $request->validate([
            'province_id' => 'required_without:district_id|exists:provinces,id',
            'district_id' => 'required_without:province_id|exists:districts,id',
            'search' => 'nullable|string|max:255',
        ]);

        if ($request->filled('search')) {
            $schools = $this->schoolService->search(
                $request->input('search'),
                $request->integer('district_id') ?: null,
                $request->integer('province_id') ?: null,
            );
        } elseif ($request->filled('province_id')) {
            $schools = $this->schoolService->getByProvince((int) $request->input('province_id'));
        } else {
            $schools = $this->schoolService->getByDistrict((int) $request->input('district_id'));
        }

        return SchoolResource::collection($schools);
    }
}
