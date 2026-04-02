<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission('leads.view', 'leads.view.own');
    }

    public function view(User $user, Client $client): bool
    {
        if ($user->hasPermission('leads.view')) {
            return true;
        }

        if ($user->hasPermission('leads.view.own')) {
            return $client->assigned_user_id === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('leads.create');
    }

    public function update(User $user, Client $client): bool
    {
        if ($user->hasPermission('leads.edit')) {
            return true;
        }

        if ($user->hasPermission('leads.view.own') && $client->assigned_user_id === $user->id) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->hasPermission('leads.delete');
    }

    public function assign(User $user): bool
    {
        return $user->hasPermission('leads.assign');
    }
}
