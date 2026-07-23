<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherActivity;
use Illuminate\Http\Request;

class ActivityHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $teacherId = $request->user()->id;
        return TeacherActivity::where('teacher_id', $teacherId)->latest('timestamp')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $teacherId = $request->user()->id;
        return TeacherActivity::create(array_merge($request->all(), ['teacher_id' => $teacherId]));
    }

    /**
     * Display the specified resource.
     */
    public function show(TeacherActivity $teacherActivity)
    {
        return $teacherActivity;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TeacherActivity $teacherActivity)
    {
        $teacherActivity->update($request->all());
        return $teacherActivity;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeacherActivity $teacherActivity)
    {
        $teacherActivity->delete();
        return response()->noContent();
    }
}
