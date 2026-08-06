<?php

namespace App\Policies;

use App\Models\PlanItem;
use App\Models\User;

class PlanItemPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, PlanItem $planItem): bool
    {
        return true;
    }

    public function create(?User $user): bool
    {
        return true;
    }

    public function update(?User $user, PlanItem $planItem): bool
    {
        return true;
    }

    public function delete(?User $user, PlanItem $planItem): bool
    {
        return true;
    }
}
