<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Auth\Access\HandlesAuthorization;

class WalletPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_wallet');
    }

    /**
     * Determine whether the wallet can view the model.
     */
    public function view(User $user, Wallet $wallet): bool
    {
        return $user->can('view_wallet');
    }

    /**
     * Determine whether the wallet can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_wallet');
    }

    /**
     * Determine whether the wallet can update the model.
     */
    public function update(User $user, Wallet $wallet): bool
    {
        return $user->can('update_wallet');
    }

    /**
     * Determine whether the wallet can delete the model.
     */
    public function delete(User $user, Wallet $wallet): bool
    {
        return $user->can('delete_wallet');
    }

    /**
     * Determine whether the wallet can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_wallet');
    }

    /**
     * Determine whether the wallet can permanently delete.
     */
    public function forceDelete(User $user, Wallet $wallet): bool
    {
        return $user->can('{{ ForceDelete }}');
    }

    /**
     * Determine whether the wallet can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('{{ ForceDeleteAny }}');
    }

    /**
     * Determine whether the wallet can restore.
     */
    public function restore(User $user, Wallet $wallet): bool
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
    public function replicate(User $user, Wallet $wallet): bool
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
