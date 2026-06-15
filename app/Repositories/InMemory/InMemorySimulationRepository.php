<?php

namespace App\Repositories\InMemory;

use App\Models\Simulation;
use App\Repositories\Contracts\SimulationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;

/**
 * Implementación ALTERNATIVA del repositorio de simulaciones que guarda los datos
 * EN MEMORIA (en un arreglo), no en MySQL.
 *
 * Su único propósito es demostrar el POLIMORFISMO: cumple exactamente el mismo
 * contrato (SimulationRepositoryInterface) que la versión Eloquent, por lo que es
 * intercambiable con ella SIN modificar el SimulationService. También es el tipo
 * de implementación que se usaría en pruebas unitarias (sin tocar la base de datos).
 */
class InMemorySimulationRepository implements SimulationRepositoryInterface
{
    /** Almacén en memoria (no persiste entre peticiones HTTP) */
    private array $simulations = [];
    private int $nextId = 1;

    public function create(array $data): Simulation
    {
        // Crea el objeto SIN guardarlo en la base de datos
        $simulation = new Simulation($data);
        $simulation->id = $this->nextId++;
        $simulation->created_at = now();
        $simulation->updated_at = now();

        $this->simulations[] = $simulation;

        return $simulation;
    }

    public function paginateForUser(int $userId, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $items = collect($this->simulations)
            ->where('user_id', $userId)
            ->when($filters['type'] ?? null, fn ($c, $type) => $c->where('type', $type))
            ->sortByDesc('created_at')
            ->values();

        $page  = Paginator::resolveCurrentPage();
        $slice = $items->slice(($page - 1) * $perPage, $perPage)->values();

        return new Paginator($slice, $items->count(), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
        ]);
    }
}
