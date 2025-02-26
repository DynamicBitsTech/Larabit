<?php

namespace Dynamicbits\Larabit\Services;

use Carbon\Carbon;
use Dynamicbits\Larabit\Exceptions\SoftDeleteNotAppliedException;
use Exception;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Abstract base service class providing common CRUD operations and query utilities.
 *
 * This class serves as a foundation for service classes interacting with Eloquent models,
 * encapsulating common database operations such as fetching, creating, updating, and deleting records.
 * It supports soft deletes, pagination, and logging functionalities.
 */
abstract class BaseService
{
    /** @var Model The Eloquent model instance associated with this service */
    public Model $model;

    /** @var Builder The query builder instance for the model */
    public Builder $query;

    /** @var Builder Internal query builder instance for building queries */
    private Builder $builder;

    /**
     * BaseService constructor.
     *
     * @param Model $model The Eloquent model instance to be used by the service
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->query = $this->model->newQuery();
        $this->builder = $this->model->newQuery();
    }

    /**
     * Retrieve a collection or paginated list of model records.
     *
     * @param array $columns Columns to select (default: all columns)
     * @param array $relations Relations to eager load
     * @param int $pagination Number of items per page (0 for no pagination)
     * @param string $orderBy Column to order by
     * @param bool $orderByDesc Whether to order in descending order
     * @param bool $withTrash Include soft-deleted records
     * @return Collection|LengthAwarePaginator Collection of records or paginated result
     * @throws SoftDeleteNotAppliedException If $withTrash is true and soft deletes are not enabled
     */
    public function get(array $columns = ['*'], $relations = [], int $pagination = 10, string $orderBy = 'created_at', bool $orderByDesc = true, bool $withTrash = false): Collection|LengthAwarePaginator
    {
        $this->applyWithTrash($withTrash);
        $this->builder->select($columns)
            ->with($relations)
            ->orderBy($orderBy, $orderByDesc);
        return $this->fetch($pagination);
    }

    /**
     * Retrieve records matching the given criteria.
     *
     * @param array $criteria Key-value pairs for where clauses
     * @param array $columns Columns to select (default: all columns)
     * @param array $relations Relations to eager load
     * @param int $pagination Number of items per page (0 for no pagination)
     * @param string $orderBy Column to order by
     * @param bool $orderByDesc Whether to order in descending order
     * @param bool $withTrash Include soft-deleted records
     * @return Collection|LengthAwarePaginator Collection of records or paginated result
     * @throws SoftDeleteNotAppliedException If $withTrash is true and soft deletes are not enabled
     */
    public function getByCriteria(array $criteria, array $columns = ['*'], array $relations = [], int $pagination = 10, string $orderBy = 'created_at', bool $orderByDesc = true, bool $withTrash = false): Collection|LengthAwarePaginator
    {
        $this->applyWithTrash($withTrash);
        $this->builder->select($columns)
            ->with($relations)
            ->where($criteria)
            ->orderBy($orderBy, $orderByDesc);
        return $this->fetch($pagination);
    }

    /**
     * Find a single record by its ID.
     *
     * @param int|string|null $id The ID of the record
     * @param array $columns Columns to select (default: all columns)
     * @param array $relations Relations to eager load
     * @param bool $withTrash Include soft-deleted records
     * @return Model The found model instance
     * @throws SoftDeleteNotAppliedException If $withTrash is true and soft deletes are not enabled
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If no record is found
     */
    public function findById(int|string|null $id, array $columns = ['*'], array $relations = [], bool $withTrash = false): Model
    {
        return $this->findByCriteria(['id' => $id], $columns, $relations, $withTrash);
    }

    /**
     * Find a single record by its UUID.
     *
     * @param string $uuid The UUID of the record
     * @param array $columns Columns to select (default: all columns)
     * @param array $relations Relations to eager load
     * @param bool $withTrash Include soft-deleted records
     * @return Model The found model instance
     * @throws SoftDeleteNotAppliedException If $withTrash is true and soft deletes are not enabled
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If no record is found
     */
    public function findByUuid(string $uuid, array $columns = ['*'], array $relations = [], bool $withTrash = false): Model
    {
        return $this->findByCriteria(['uuid' => $uuid], $columns, $relations, $withTrash);
    }

    /**
     * Find a single record by given criteria.
     *
     * @param array $criteria Key-value pairs for where clauses
     * @param array $columns Columns to select (default: all columns)
     * @param array $relations Relations to eager load
     * @param bool $withTrash Include soft-deleted records
     * @return Model The found model instance
     * @throws SoftDeleteNotAppliedException If $withTrash is true and soft deletes are not enabled
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If no record is found
     */
    public function findByCriteria(array $criteria, array $columns = ['*'], array $relations = [], bool $withTrash = false): Model
    {
        $this->applyWithTrash($withTrash);
        return $this->builder->select($columns)
            ->with($relations)
            ->where($criteria)
            ->firstOrFail();
    }

    /**
     * Retrieve the first record matching the given criteria, or null if none found.
     *
     * @param array $criteria Key-value pairs for where clauses
     * @param array $columns Columns to select (default: all columns)
     * @return Model|null The found model instance or null
     */
    public function firstByCriteria(array $criteria, array $columns = ['*']): Model|null
    {
        return $this->newQuery()
            ->where($criteria)
            ->first($columns);
    }

    /**
     * Create a new record.
     *
     * @param array $attributes Attributes for the new record
     * @return Model The created model instance
     */
    public function create(array $attributes): Model
    {
        return $this->newQuery()
            ->create($attributes);
    }

    /**
     * Create multiple records in a single operation.
     *
     * @param array $iterableArray Array of attribute arrays for the records
     * @param bool $CreatedUpdatedBy Whether to apply created_by and updated_by fields
     * @param bool $timestamps Whether to apply created_at and updated_at timestamps
     * @return bool True if successful
     */
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
        return $this->newQuery()
            ->insert($iterableArray);
    }

    /**
     * Update an existing record or create it if it doesn't exist.
     *
     * @param array $attributes Attributes to match for update or create
     * @param array $values Values to update or set during creation
     * @return Model The updated or created model instance
     */
    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        return $this->newQuery()
            ->updateOrCreate($attributes, $values);
    }

    /**
     * Update an existing model instance.
     *
     * @param Model $model The model instance to update
     * @param array $attributes Attributes to update
     * @return bool|null True if successful, null if failed
     */
    public function update(Model $model, array $attributes): ?bool
    {
        return $model->update($attributes);
    }

    /**
     * Update records matching the given criteria.
     *
     * @param array $values Values to update
     * @param string|array|Expression $column Where clause column or expression
     * @param mixed|null $operator Where clause operator (optional)
     * @param mixed|null $value Where clause value (optional)
     * @param string $boolean Boolean operator for where clause (default: 'and')
     * @return int Number of affected rows
     */
    public function updateByCriteria(array $values, string|array|Expression $column, $operator = null, $value = null, string $boolean = 'and'): int
    {
        return $this->newQuery()
            ->where($column, $operator, $value, $boolean)
            ->update($values);
    }

    /**
     * Insert or update multiple records based on unique keys.
     *
     * @param array $iterableArray Array of attribute arrays for the records
     * @param array|string $uniqueBy Column(s) to determine uniqueness
     * @param array|null $update Columns to update if record exists (optional)
     * @param bool $CreatedUpdatedBy Whether to apply created_by and updated_by fields
     * @param bool $timestamps Whether to apply timestamps (ignored in this method)
     * @return int Number of affected rows
     */
    public function upsert(array $iterableArray, array|string $uniqueBy, array|null $update = null, bool $CreatedUpdatedBy = true, bool $timestamps = true): int
    {
        if ($CreatedUpdatedBy) {
            $iterableArray = array_map(function ($attributes) {
                return $this->applyCreatedUpdateBy($attributes);
            }, $iterableArray);
        }
        return $this->newQuery()
            ->upsert($iterableArray, $uniqueBy, $update);
    }

    /**
     * Retrieve a collection of values for a specific column.
     *
     * @param string $column The column to pluck
     * @param string|null $key Optional column to use as keys
     * @return SupportCollection Collection of plucked values
     */
    public function pluck(string $column, string|null $key = null): SupportCollection
    {
        return $this->newQuery()
            ->pluck($column, $key);
    }

    /**
     * Retrieve a collection of values for a specific column matching criteria.
     *
     * @param array $criteria Key-value pairs for where clauses
     * @param string $column The column to pluck
     * @param string|null $key Optional column to use as keys
     * @return SupportCollection Collection of plucked values
     */
    public function pluckByCriteria(array $criteria, string $column, string|null $key = null): SupportCollection
    {
        return $this->newQuery()
            ->where($criteria)
            ->pluck($column, $key);
    }

    /**
     * Delete a model instance.
     *
     * @param Model $model The model instance to delete
     * @return bool|null True if successful, null if failed
     */
    public function delete(Model $model): ?bool
    {
        return $model->delete();
    }

    /**
     * Delete records matching the given criteria.
     *
     * @param string|array|Expression $column Where clause column or expression
     * @param mixed|null $operator Where clause operator (optional)
     * @param mixed|null $value Where clause value (optional)
     * @param string $boolean Boolean operator for where clause (default: 'and')
     * @return mixed Number of deleted rows
     */
    public function deleteByCriteria(string|array|Expression $column, $operator = null, $value = null, string $boolean = 'and'): mixed
    {
        return $this->newQuery()
            ->where($column, $operator, $value, $boolean)
            ->delete();
    }

    /**
     * Permanently delete a model instance.
     *
     * @param Model $model The model instance to force delete
     * @return bool|null True if successful, null if failed
     */
    public function forceDelete(Model $model): ?bool
    {
        return $model->forceDelete();
    }

    /**
     * Restore a soft-deleted model instance.
     *
     * @param Model $model The model instance to restore
     * @return bool True if successful
     * @throws SoftDeleteNotAppliedException If soft deletes are not enabled
     */
    public function restore(Model $model): bool
    {
        return $this->hasSoftDelete() ? $model->restore() : throw new SoftDeleteNotAppliedException($this->model);
    }

    /**
     * Retrieve soft-deleted records.
     *
     * @param array $columns Columns to select (default: all columns)
     * @param array $relations Relations to eager load
     * @param int $pagination Number of items per page (0 for no pagination)
     * @param string $orderBy Column to order by
     * @param bool $orderByDesc Whether to order in descending order
     * @return Collection|LengthAwarePaginator Collection of trashed records or paginated result
     * @throws SoftDeleteNotAppliedException If soft deletes are not enabled
     */
    public function trash($columns = ['*'], $relations = [], int $pagination = 10, string $orderBy = 'deleted_at', bool $orderByDesc = true): Collection|LengthAwarePaginator
    {
        $this->applyOnlyTrash();
        $this->builder->select($columns)
            ->with($relations)
            ->orderBy($orderBy, $orderByDesc);
        return $this->fetch($pagination);
    }

    /**
     * Create a new query builder instance for the model.
     *
     * @return Builder A fresh query builder instance
     */
    public function newQuery(): Builder
    {
        return $this->model->newQuery();
    }

    /**
     * Fetch results based on pagination settings.
     *
     * @param int $pagination Number of items per page (0 for no pagination)
     * @return LengthAwarePaginator|Collection Paginated result or collection
     */
    private function fetch(int $pagination): LengthAwarePaginator|Collection
    {
        return $pagination ? $this->builder->paginate($pagination) : $this->builder->get();
    }

    /**
     * Apply soft delete scoping to include trashed records if requested.
     *
     * @param bool $withTrash Whether to include soft-deleted records
     * @throws SoftDeleteNotAppliedException If soft deletes are not enabled
     */
    private function applyWithTrash(bool $withTrash = false): void
    {
        if ($withTrash) {
            $this->hasSoftDelete() ? $this->builder->withTrashed($withTrash) : throw new SoftDeleteNotAppliedException($this->model);
        }
    }

    /**
     * Check if the model uses soft deletes.
     *
     * @return bool True if soft deletes are enabled
     */
    private function hasSoftDelete(): bool
    {
        return $this->model->hasGlobalScope(SoftDeletingScope::class);
    }

    /**
     * Apply soft delete scoping to only retrieve trashed records.
     *
     * @throws SoftDeleteNotAppliedException If soft deletes are not enabled
     */
    private function applyOnlyTrash(): void
    {
        $this->hasSoftDelete() ? $this->builder->onlyTrashed() : throw new SoftDeleteNotAppliedException($this->model);
    }

    /**
     * Apply timestamps to attributes.
     *
     * @param array $attributes Attributes to modify
     * @return array Modified attributes with timestamps
     */
    private function applyTimestamps(array $attributes): array
    {
        $attributes['created_at'] = Carbon::now();
        $attributes['updated_at'] = Carbon::now();
        return $attributes;
    }

    /**
     * Apply created_by and updated_by fields to attributes.
     *
     * @param array $attributes Attributes to modify
     * @return array Modified attributes with user IDs
     */
    private function applyCreatedUpdateBy(array $attributes): array
    {
        $attributes['created_by'] = Auth::id();
        $attributes['updated_by'] = Auth::id();
        return $attributes;
    }

    /**
     * Log an error with exception details.
     *
     * @param Exception $e The exception to log
     * @param string $message The log message
     * @param mixed|null $data Additional data to log
     */
    public function logError(Exception $e, string $message, $data = null): void
    {
        Log::error($message, [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'data' => $data
        ]);
    }

    /**
     * Log an informational message.
     *
     * @param string $message The log message
     * @param mixed|null $data Additional data to log
     */
    public function logInfo(string $message, $data = null): void
    {
        Log::info($message, [
            'data' => $data
        ]);
    }

    /**
     * Log a warning message.
     *
     * @param string $message The log message
     * @param mixed|null $data Additional data to log
     */
    public function logWarning(string $message, $data = null): void
    {
        Log::warning($message, [
            'data' => $data
        ]);
    }

    /**
     * Log a debug message.
     *
     * @param string $message The log message
     * @param mixed|null $data Additional data to log
     */
    public function logDebug(string $message, $data = null): void
    {
        Log::debug($message, [
            'data' => $data
        ]);
    }
}
