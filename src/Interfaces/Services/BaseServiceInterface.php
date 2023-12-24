<?php

namespace Dynamicbits\Larabit\Interfaces\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

interface BaseServiceInterface
{
    /**
     * @param array $columns
     * @param array $relations
     * @param int|bool $pagination
     * @return Collection|LengthAwarePaginator
     */
    public function get(array $columns = ['*'], array $relations = [], int|bool $pagination = 10): Collection|LengthAwarePaginator;

    /**
     * @param int   $id
     * @param array $columns
     * @param array $relations
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findById(int $id, array $columns = ['*'], array $relations = []): Model;

    /**
     * @param string $uuid
     * @param array  $columns
     * @param array  $relations
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findByUuid(string $uuid, array $columns = ['*'], array $relations = []): Model;

    /**
     * @param array $criteria
     * @param array $columns
     * @param array $relations
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findByCriteria(array $criteria, array $columns = ['*'], array $relations = []): Model;

    /**
     * @param array $criteria
     * @param array $columns
     * @param array $relations
     * @param int|bool $pagination
     * @return Collection|LengthAwarePaginator
     */
    public function getByCriteria(array $criteria, array $columns = ['*'], array $relations = [], int|bool $pagination = 10): Collection|LengthAwarePaginator;

    /**
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model;

    /**
     * @param Model $model
     * @param array              $attributes
     * @return bool|null
     */
    public function update(Model $model, array $attributes): ?bool;

    /**
     * @param Model $model
     * @return bool|null
     */
    public function delete(Model $model): ?bool;

    /**
     * @return Builder
     */
    public function newQuery(): Builder;
}
