<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SoundResource;
use App\Models\Sound;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SoundController extends Controller
{
    public function index(): ResourceCollection
    {
        $sounds = Sound::query()
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return SoundResource::collection($sounds);
    }
}
