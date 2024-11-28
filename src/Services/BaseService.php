<?php

namespace Dynamicbits\Larabit\Services;

use Carbon\Carbon;
use Dynamicbits\Larabit\Exceptions\SoftDeleteNotAppliedException;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;

abstract class BaseService
{
    public Model $model;
    public Builder $query;
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->query = $this->model->newQuery();
    }
    public function get(array $columns = ['*'], $relations = [], int $pagination = 10, string $orderBy = 'created_at', bool $orderByDesc = true, bool $withTrash = false): Collection|LengthAwarePaginator
    {
        $this->applyWithTrash($withTrash);
        $this->newQuery()->select($columns)->with($relations);
        $this->orderBy($orderBy, $orderByDesc);
        return $this->fetch($pagination);
    }
    public function getByCriteria(array $criteria, array $columns = ['*'], array $relations = [], int $pagination = 10, string $orderBy = 'created_at', bool $orderByDesc = true, bool $withTrash = false): Collection|LengthAwarePaginator
    {
        $this->applyWithTrash();
        $this->newQuery()->select($columns)->with($relations)->where($criteria);
        $this->orderBy($orderBy, $orderByDesc);
        return $this->fetch($pagination);
    }
    public function findById(int|string|null $id, array $columns = ['*'], array $relations = [], bool $withTrash = false): Model
    {
        return $this->findByCriteria(['id' => $id], $columns, $relations, $withTrash);
    }
    public function findByUuid(string $uuid, array $columns = ['*'], array $relations = [], bool $withTrash = false): Model
    {
        return $this->findByCriteria(['uuid' => $uuid], $columns, $relations, $withTrash);
    }
    public function findByCriteria(array $criteria, array $columns = ['*'], array $relations = [], bool $withTrash = false): Model
    {
        $this->applyWithTrash($withTrash);
        return $this->newQuery()->select($columns)->with($relations)->where($criteria)->firstOrFail();
    }
    public function firstByCriteria(array $criteria, array $columns = ['*']): Model|null
    {
        return $this->newQuery()->where($criteria)->first($columns);
    }
    public function create(array $attributes): Model
    {
        return $this->newQuery()->create($attributes);
    }
    public function createMany(array $iterableArray, bool $CreatedUpdatedBy = true, bool $timestamps = true): bool
    {
        if ($CreatedUpdatedBy) {
            $iterableArray = array_map(function ($attributes) {
                return $this->applyCreatedUpdateBy($attributes);
            }, $iterableArray);
        }
        if ($timestamps) {
            $iterableArray = array_map(function ($attributes) {
                return $this->applyTimestamps($attributes);
            }, $iterableArray);
        }
        return $this->newQuery()->insert($iterableArray);
    }
    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        return $this->newQuery()->updateOrCreate($attributes, $values);
    }
    public function update(Model $model, array $attributes): ?bool
    {
        return $model->update($attributes);
    }
    public function updateByCriteria(array $values, string|array|Expression $column, $operator = null, $value = null, string $boolean = 'and'): int
    {
        return $this->newQuery()->where($column, $operator, $value, $boolean)->update($values);
    }
    public function upsert(array $iterableArray, array|string $uniqueBy, array|null $update = null, bool $CreatedUpdatedBy = true, bool $timestamps = true): int
    {
        if ($CreatedUpdatedBy) {
            $iterableArray = array_map(function ($attributes) {
                return $this->applyCreatedUpdateBy($attributes);
            }, $iterableArray);
        }
        return $this->newQuery()->upsert($iterableArray, $uniqueBy, $update);
    }
    public function pluck(string $column, string|null $key = null): SupportCollection
    {
        return $this->newQuery()->pluck($column, $key);
    }
    public function pluckByCriteria(array $criteria, string $column, string|null $key = null): SupportCollection
    {
        $this->newQuery()->where($criteria);
        return $this->pluck($column, $key);
    }
    public function delete(Model $model): ?bool
    {
        return $model->delete();
    }
    public function deleteByCriteria(string|array|Expression $column, $operator = null, $value = null, string $boolean = 'and'): mixed
    {
        return $this->newQuery()->where($column, $operator, $value, $boolean)->delete();
    }
    public function forceDelete(Model $model): ?bool
    {
        return $model->forceDelete();
    }
    public function restore(Model $model): bool
    {
        return $this->hasSoftDelete() ? $model->restore() : throw new SoftDeleteNotAppliedException($this->model);
    }
    public function trash($columns = ['*'], $relations = [], int $pagination = 10, string $orderBy = 'deleted_at', bool $orderByDesc = true): Collection|LengthAwarePaginator
    {
        $this->applyOnlyTrash();
        $this->newQuery()->select($columns)->with($relations);
        $this->orderBy($orderBy, $orderByDesc);
        return $this->fetch($pagination);
    }
    public function newQuery(): Builder
    {
        return $this->model->newQuery();
    }
    public function orderBy(string $column, bool $orderByDesc): void
    {
        $direction = $orderByDesc ? 'desc' : 'asc';
        $this->newQuery()->orderBy($column, $direction);
    }
    private function fetch(int $pagination): LengthAwarePaginator|Collection
    {
        return $pagination ? $this->newQuery()->paginate($pagination) : $this->newQuery()->get();
    }
    private function applyWithTrash(bool $withTrash = false): void
    {
        if ($withTrash) {
            $this->hasSoftDelete() ? $this->newQuery()->withTrashed($withTrash) : throw new SoftDeleteNotAppliedException($this->model);
        }
    }
    private function hasSoftDelete(): bool
    {
        return $this->model->hasGlobalScope(SoftDeletingScope::class);
    }
    private function applyOnlyTrash(): void
    {
        $this->hasSoftDelete() ? $this->newQuery()->onlyTrashed() : throw new SoftDeleteNotAppliedException($this->model);
    }
    private function applyTimestamps(array $attributes): array
    {
        $attributes['created_at'] = Carbon::now();
        $attributes['updated_at'] = Carbon::now();
        return $attributes;
    }
    private function applyCreatedUpdateBy(array $attributes): array
    {
        $attributes['created_by'] = Auth::id();
        $attributes['updated_by'] = Auth::id();
        return $attributes;
    }
}
