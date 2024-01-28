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

    public function get($columns = ['*'], $relations = [], int|bool $pagination = 10, string $orderBy = 'created_at', bool $orderByDesc = true, bool $withTrash = false): Collection|LengthAwarePaginator
    {
        $query = $this->withTrash($withTrash);
        $query = $query->select($columns)->with($relations);
        $query = $this->orderBy($query, $orderBy, $orderByDesc);
        return $this->pagination($query, $pagination);
    }

    public function findById(int $id, array $columns = ['*'], array $relations = [], bool $withTrash = false): Model
    {
        return $this->findByCriteria(['id' => $id], $columns, $relations, $withTrash);
    }

    public function findByUuid(string $uuid, array $columns = ['*'], array $relations = [], bool $withTrash = false): Model
    {
        return $this->findByCriteria(['uuid' => $uuid], $columns, $relations, $withTrash);
    }

    public function findByCriteria(array $criteria, array $columns = ['*'], array $relations = [], bool $withTrash = false): Model
    {
        $query = $this->withTrash($withTrash);
        return $query->select($columns)
            ->with($relations)
            ->where($criteria)
            ->firstOrFail();
    }

    public function getByCriteria(array $criteria, array $columns = ['*'], array $relations = [], int|bool $pagination = 10, string $orderBy = 'created_at', bool $orderByDesc = true, bool $withTrash = false): Collection|LengthAwarePaginator
    {
        $query = $this->withTrash($withTrash);
        $query = $query->select($columns)->with($relations)->where($criteria);
        $query = $this->orderBy($query, $orderBy, $orderByDesc);
        return $this->pagination($query, $pagination);
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

    public function restore(Model $model): ?bool
    {
        return $model->restore();
    }

    public function forceDelete(Model $model): ?bool
    {
        return $model->forceDelete();
    }

    public function trash($columns = ['*'], $relations = [], int|bool $pagination = 10, string $orderBy = 'deleted_at', bool $orderByDesc = true): Collection|LengthAwarePaginator
    {
        $query = $this->newQuery()->onlyTrashed();
        $query = $query->select($columns)->with($relations);
        $query = $this->orderBy($query, $orderBy, $orderByDesc);
        return $this->pagination($query, $pagination);
    }

    public function newQuery(): Builder
    {
        return $this->model->newQuery();
    }

    private function withTrash(bool $withTrash): Builder
    {
        return $withTrash ? $this->newQuery()->withTrashed() : $this->newQuery();
    }

    private function orderBy(Builder $query, string $orderBy, bool $orderByDesc): Builder
    {
        return $orderByDesc ? $query->orderByDesc($orderBy) : $query->orderBy($orderBy);
    }

    private function pagination(Builder $query, int|bool $pagination): Collection|LengthAwarePaginator
    {
        return $pagination ? $query->paginate($pagination) : $query->get();
    }
}
