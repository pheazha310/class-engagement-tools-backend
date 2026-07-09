<?php

namespace App\Repositories;

use App\Models\SchoolRequest;
use App\Repositories\Contracts\SchoolRequestRepositoryInterface;

class SchoolRequestRepository implements SchoolRequestRepositoryInterface
{
    public function create(array $data): SchoolRequest
    {
        return SchoolRequest::create($data);
    }
}
