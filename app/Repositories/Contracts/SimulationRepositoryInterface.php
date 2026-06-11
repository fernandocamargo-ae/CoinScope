<?php

namespace App\Repositories\Contracts;

use App\Models\Simulation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface SimulationRepositoryInterface
{
    public function create(array $data): Simulation;

    public function paginateForUser(int $userId, array $filters = [], int $perPage = 10): LengthAwarePaginator;
}
