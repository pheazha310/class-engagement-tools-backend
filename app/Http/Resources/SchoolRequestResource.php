<?php

namespace App\Http\Resources;

use App\Models\SchoolRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SchoolRequest */
class SchoolRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'school_name' => $this->school_name,
            'address' => $this->address,
            'phone' => $this->phone,
            'notes' => $this->notes,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
