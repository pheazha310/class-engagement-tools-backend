<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSchoolRequestRequest;
use App\Http\Resources\SchoolRequestResource;
use App\Models\SchoolRequest;

class SchoolRequestController extends Controller
{
    public function store(StoreSchoolRequestRequest $request)
    {
        $schoolRequest = SchoolRequest::create([
            ...$request->validated(),
            'user_id' => $request->user()?->id,
        ]);

        return new SchoolRequestResource($schoolRequest);
    }
}
