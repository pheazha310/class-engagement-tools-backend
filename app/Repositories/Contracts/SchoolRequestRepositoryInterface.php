<?php

namespace App\Repositories\Contracts;

use App\Models\SchoolRequest;

interface SchoolRequestRepositoryInterface
{
    public function create(array $data): SchoolRequest;
}
