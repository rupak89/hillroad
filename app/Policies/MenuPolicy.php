<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;

class MenuPolicy
{
    public function view(User $user, Menu $menu): bool
    {
        return $this->owns($user, $menu);
    }

    public function update(User $user, Menu $menu): bool
    {
        return $this->owns($user, $menu);
    }

    public function delete(User $user, Menu $menu): bool
    {
        return $this->owns($user, $menu);
    }

    public function duplicate(User $user, Menu $menu): bool
    {
        return $this->owns($user, $menu);
    }

    public function viewCostBreakdown(User $user, Menu $menu): bool
    {
        return $this->owns($user, $menu);
    }

    public function print(User $user, Menu $menu): bool
    {
        return $this->owns($user, $menu);
    }

    private function owns(User $user, Menu $menu): bool
    {
        return (int) $menu->user_id === (int) $user->id;
    }
}
