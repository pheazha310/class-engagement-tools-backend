<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationSchoolController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'province_id' => 'required|exists:provinces,id',
            'search' => 'nullable|string|max:255',
        ]);

        $query = School::query()->where('province_id', $request->integer('province_id'));

        if ($request->filled('country')) {
            $query->where('country', $request->input('country'));
        }

        if ($request->filled('search')) {
            $query->where('school_name', 'like', '%'.$request->input('search').'%');
        }

        $schools = $query->orderBy('school_name')->get(['id', 'school_name']);

        return response()->json(
            $schools->map(fn (School $school): array => [
                'id' => $school->id,
                'name' => $school->school_name,
            ])
        );
    }
}
