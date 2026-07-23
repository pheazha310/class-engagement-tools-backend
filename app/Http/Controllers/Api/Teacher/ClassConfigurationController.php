<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassConfiguration;
use Illuminate\Http\Request;

class ClassConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $teacherId = $request->user()->id;
        return ClassConfiguration::where('teacher_id', $teacherId)->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $teacherId = $request->user()->id;
        return ClassConfiguration::create(array_merge($request->all(), ['teacher_id' => $teacherId]));
    }

    /**
     * Display the specified resource.
     */
    public function show(ClassConfiguration $classConfiguration)
    {
        return $classConfiguration;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClassConfiguration $classConfiguration)
    {
        $classConfiguration->update($request->all());
        return $classConfiguration;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClassConfiguration $classConfiguration)
    {
        $classConfiguration->delete();
        return response()->noContent();
    }
}
