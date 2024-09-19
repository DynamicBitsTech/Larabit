<?php

namespace Dynamicbits\Larabit\Services;

use App\Models\User;
use Dynamicbits\Larabit\Helpers\TraitChecker;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BaseUserService extends BaseService
{
    public function __construct(
        User $model
    ) {
        parent::__construct($model);
    }
    public function hasRole(array|string $roles): ?bool
    {
        $this->hasSpatie();
        return Auth::user()?->hasRole($roles);
    }
    public function getByRole(array|string $roles, $columns = ['*'], $relations = [], int $pagination = 10, string $orderBy = 'created_at', bool $orderByDesc = true, bool $withTrash = false): Collection|LengthAwarePaginator
    {
        $this->hasSpatie();
        $this->query->role($roles);
        return $this->get($columns, $relations, $pagination, $orderBy, $orderByDesc, $withTrash);
    }
    private function hasSpatie()
    {
        TraitChecker::check(User::class, \Spatie\Permission\Traits\HasRoles::class);
    }
}