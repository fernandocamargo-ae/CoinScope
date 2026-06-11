<?php

namespace App\Repositories\Eloquent;

use App\Models\Simulation;
use App\Repositories\Contracts\SimulationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SimulationRepository implements SimulationRepositoryInterface
{
    public function create(array $data): Simulation
    {
        return Simulation::create($data);
    }

    public function paginateForUser(int $userId, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Simulation::where('user_id', $userId)
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->with(['sourceCrypto', 'targetCrypto'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
