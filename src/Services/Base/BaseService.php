<?php

namespace Dynamicbits\Larabit\Services\Base;

use Dynamicbits\Larabit\Repositories\Base\BaseRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BaseService implements BaseServiceInterface
{
    /**
     * @var BaseRepositoryInterface
     */
    public function __construct(
        private BaseRepositoryInterface $repo
    ) {
    }

    public function get(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->repo->get($columns, $relations);
    }

    public function findById(int $id, array $columns = ['*'], array $relations = []): Model
    {
        return $this->repo->findById($id, $columns, $relations);
    }

    public function findByUuid(string $uuid, array $columns = ['*'], array $relations = []): Model
    {
        return $this->repo->findByUuid($uuid, $columns, $relations);
    }

    public function findByCriteria(array $criteria, array $columns = ['*'], array $relations = []): Model
    {
        return $this->repo->findByCriteria($criteria, $columns, $relations);
    }

    public function getByCriteria(array $criteria, array $columns = ['*'], array $relations = []): Collection
    {
        return $this->repo->getByCriteria($criteria, $columns, $relations);
    }

    public function create(array $attributes): Model
    {
        return $this->repo->create($attributes);
    }

    public function update(Model $model, array $attributes): ?bool
    {
        return $this->repo->update($model, $attributes);
    }

    public function delete(Model $model): ?bool
    {
        return $this->repo->delete($model);
    }

    public function newQuery(): Builder
    {
        return $this->repo->newQuery();
    }
}
