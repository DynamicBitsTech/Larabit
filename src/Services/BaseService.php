<?php

namespace Dynamicbits\Larabit\Services;

use Dynamicbits\Larabit\Interfaces\Services\BaseServiceInterface;
use Dynamicbits\Larabit\Repositories\BaseRepository;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class BaseService implements BaseServiceInterface
{
    /**
     * @var BaseRepository $repo The repository instance used by the service.
     */
    private BaseRepository $repo;

    /**
     * BaseService constructor.
     *
     * @param Model $model The Eloquent model associated with this service.
     */
    public function __construct(Model $model)
    {
        $this->repo = new BaseRepository($model);
    }

    public function get($columns = ['*'], $relations = [], int|bool $pagination = 10, string $orderBy = 'created_at', bool $orderByDesc = true, bool $withTrash = false): Collection|LengthAwarePaginator
    {
        return $this->repo->get($columns, $relations, $pagination, $orderBy, $orderByDesc, $withTrash);
    }

    public function findById(int $id, array $columns = ['*'], array $relations = [], bool $withTrash = false): Model
    {
        return $this->repo->findById($id, $columns, $relations, $withTrash);
    }

    public function findByUuid(string $uuid, array $columns = ['*'], array $relations = [], bool $withTrash = false): Model
    {
        return $this->repo->findByUuid($uuid, $columns, $relations, $withTrash);
    }

    public function findByCriteria(array $criteria, array $columns = ['*'], array $relations = [], bool $withTrash = false): Model
    {
        return $this->repo->findByCriteria($criteria, $columns, $relations, $withTrash);
    }

    public function getByCriteria(array $criteria, array $columns = ['*'], array $relations = [], int|bool $pagination = 10, string $orderBy = 'created_at', bool $orderByDesc = true, bool $withTrash = false): Collection|LengthAwarePaginator
    {
        return $this->repo->getByCriteria($criteria, $columns, $relations, $pagination, $orderBy, $orderByDesc, $withTrash);
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

    public function trash($columns = ['*'], $relations = [], bool|int $pagination = 10, string $orderBy = 'deleted_at', bool $orderByDesc = true): Collection|LengthAwarePaginator
    {
        return $this->repo->trash($columns, $relations, $pagination, $orderBy, $orderByDesc);
    }

    public function restore(Model $model): ?bool
    {
        return $this->repo->restore($model);
    }

    public function forceDelete(Model $model): ?bool
    {
        return $this->repo->forceDelete($model);
    }

    public function newQuery(): Builder
    {
        return $this->repo->newQuery();
    }
}
