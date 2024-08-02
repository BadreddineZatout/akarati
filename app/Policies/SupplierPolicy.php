<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SupplierPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_supplier');
    }

    /**
     * Determine whether the supplier can view the model.
     */
    public function view(User $user, Supplier $supplier): bool
    {
        return $user->can('view_supplier');
    }

    /**
     * Determine whether the supplier can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_supplier');
    }

    /**
     * Determine whether the supplier can update the model.
     */
    public function update(User $user, Supplier $supplier): bool
    {
        return $user->can('update_supplier');
    }

    /**
     * Determine whether the supplier can delete the model.
     */
    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->can('delete_supplier');
    }

    /**
     * Determine whether the supplier can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_supplier');
    }

    /**
     * Determine whether the supplier can permanently delete.
     */
    public function forceDelete(User $user, Supplier $supplier): bool
    {
        return $user->can('{{ ForceDelete }}');
    }

    /**
     * Determine whether the supplier can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('{{ ForceDeleteAny }}');
    }

    /**
     * Determine whether the supplier can restore.
     */
    public function restore(User $user, Supplier $supplier): bool
    {
        return $user->can('{{ Restore }}');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('{{ RestoreAny }}');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Supplier $supplier): bool
    {
        return $user->can('{{ Replicate }}');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('{{ Reorder }}');
    }
}
