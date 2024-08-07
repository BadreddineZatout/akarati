<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_client');
    }

    /**
     * Determine whether the client can view the model.
     */
    public function view(User $user, Client $client): bool
    {
        return $user->can('view_client');
    }

    /**
     * Determine whether the client can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_client');
    }

    /**
     * Determine whether the client can update the model.
     */
    public function update(User $user, Client $client): bool
    {
        return $user->can('update_client');
    }

    /**
     * Determine whether the client can delete the model.
     */
    public function delete(User $user, Client $client): bool
    {
        return $user->can('delete_client');
    }

    /**
     * Determine whether the client can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_client');
    }

    /**
     * Determine whether the client can permanently delete.
     */
    public function forceDelete(User $user, Client $client): bool
    {
        return $user->can('{{ ForceDelete }}');
    }

    /**
     * Determine whether the client can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('{{ ForceDeleteAny }}');
    }

    /**
     * Determine whether the client can restore.
     */
    public function restore(User $user, Client $client): bool
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
    public function replicate(User $user, Client $client): bool
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
