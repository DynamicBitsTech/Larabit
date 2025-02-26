<?php

namespace Dynamicbits\Larabit\Services;

use App\Models\User;
use BackedEnum;
use Dynamicbits\Larabit\Helpers\TraitChecker;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * Service class for handling user-related operations.
 */
class BaseUserService extends BaseService
{
    /**
     * BaseUserService constructor.
     *
     * @param User $model The User model instance.
     */
    public function __construct(
        User $model
    ) {
        parent::__construct($model);
    }

    /**
     * Check if the authenticated user has a specific role.
     *
     * @param array|BackedEnum|Collection|int|string $roles The role(s) to check.
     * @param string|null $guard The authentication guard (optional).
     * @return bool|null Returns true if the user has the role, false otherwise, or null if no user is authenticated.
     */
    public function hasRole(array|BackedEnum|Collection|int|string $roles, string|null $guard = null): ?bool
    {
        $this->hasSpatie();
        return Auth::user()?->hasRole($roles, $guard);
    }

    /**
     * Retrieve users by their assigned roles.
     *
     * @param array|string $roles The role(s) to filter users by.
     * @param array $columns The columns to select (default: all columns).
     * @param array $relations The relationships to eager load.
     * @param int $pagination The number of records per page (default: 10). Set to 0 for no pagination.
     * @param string $orderBy The column to order results by (default: 'created_at').
     * @param bool $orderByDesc Whether to order results in descending order (default: true).
     * @param bool $withTrash Whether to include soft-deleted users (default: false).
     * @return Collection|LengthAwarePaginator A collection of users or a paginated result.
     */
    public function getByRole(array|string $roles, $columns = ['*'], $relations = [], int $pagination = 10, string $orderBy = 'created_at', bool $orderByDesc = true, bool $withTrash = false): Collection|LengthAwarePaginator
    {
        $this->hasSpatie();
        $this->query->role($roles);
        return $this->get($columns, $relations, $pagination, $orderBy, $orderByDesc, $withTrash);
    }

    /**
     * Ensure that the User model uses the Spatie roles and permissions trait.
     *
     * @return void
     */
    private function hasSpatie(): void
    {
        TraitChecker::has(User::class, \Spatie\Permission\Traits\HasRoles::class);
    }
}
