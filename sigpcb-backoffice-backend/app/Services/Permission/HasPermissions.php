<?php

namespace App\Services\Permission;

use App\Models\User;

trait HasPermissions
{
    /**
     * Empêche l'exécution  du code si l'utilisateur n'a pas les accès suffisant
     *
     * @param array $permissions
     * @param string $message Le message à afficher
     * @param User|null $user L''utilisateur concerné ou connecté
     * @return void
     */
    protected function hasAnyPermission(array $permissions = [], string $message = "Vous n'êtes pas autorisé (e) à accéder à cette page", ?User $user = null): void
    {
        if ($permissions) {
            if ($user === null) {
                /** @var User */
                $user = auth()->user();
            }

            if (!$user->canAny($permissions)) {
                throw new UserHasNotAccessException($message, 1);
            }
        }
    }

    /**
     * Empêche l'exécution  du code si l'utilisateur n'a pas les accès suffisant
     *
     * @param array $permissions
     * @param string $message Le message à afficher
     * @param User|null $user L''utilisateur concerné ou connecté
     * @return void
     */
    protected function hasPermission(array $permissions = [], string $message = "Vous n'êtes pas autorisé (e) à accéder à cette page", ?User $user = null): void
    {
        if ($permissions) {
            if ($user === null) {
                /** @var User */
                $user = auth()->user();
            }

            if (!$user->can($permissions)) {
                throw new UserHasNotAccessException($message, 1);
            }
        }
    }
}
