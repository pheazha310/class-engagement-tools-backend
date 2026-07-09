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
            'district_id' => 'required|exists:districts,id',
            'search' => 'nullable|string|max:255',
        ]);

        if ($request->filled('search')) {
            $schools = $this->schoolService->search($request->input('search'), (int) $request->input('district_id'));
        } else {
            $schools = $this->schoolService->getByDistrict((int) $request->input('district_id'));
        }

        return SchoolResource::collection($schools);
    }
}
