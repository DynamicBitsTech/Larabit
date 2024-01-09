<?php

namespace Dynamicbits\Larabit\Repositories;

use Dynamicbits\Larabit\Interfaces\Repositories\BaseRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

class BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var Model
     */
    public function __construct(
        private Model $model
    ) {
    }

    public function get($columns = ['*'], $relations = [], int|bool $pagination = 10, string $orderBy = 'created_at', bool $orderByDesc = true): Collection|LengthAwarePaginator
    {
        $query = $this->model->select($columns)->with($relations);
        $query = $orderByDesc ? $query->orderByDesc($orderBy) : $query->orderBy($orderBy);
        return $pagination ? $query->paginate($pagination) : $query->get();
    }

    public function findById(int $id, array $columns = ['*'], array $relations = []): Model
    {
        return $this->findByCriteria(['id' => $id], $columns, $relations);
    }

    public function findByUuid(string $uuid, array $columns = ['*'], array $relations = []): Model
    {
        return $this->findByCriteria(['uuid' => $uuid], $columns, $relations);
    }

    public function findByCriteria(array $criteria, array $columns = ['*'], array $relations = []): Model
    {
        return $this->newQuery()
            ->select($columns)
            ->with($relations)
            ->where($criteria)
            ->firstOrFail();
    }

    public function getByCriteria(array $criteria, array $columns = ['*'], array $relations = [], int|bool $pagination = 10, string $orderBy = 'created_at', bool $orderByDesc = true): Collection|LengthAwarePaginator
    {
        $query = $this->model->newQuery()->select($columns)->with($relations)->where($criteria);
        $query = $orderByDesc ? $query->orderByDesc($orderBy) : $query->orderBy($orderBy);
        return $pagination ? $query->paginate($pagination) : $query->get();
    }

    public function create(array $attributes): Model
    {
        return $this->newQuery()
            ->create($attributes);
    }

    public function update(Model $model, array $attributes): ?bool
    {
        return $model->update($attributes);
    }

    public function delete(Model $model): ?bool
    {
        return $model->delete();
    }

    public function newQuery(): Builder
    {
        return $this->model->newQuery();
    }
}
