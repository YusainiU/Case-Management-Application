<?php

namespace Simplydigital\StockManagement\Policies;

use App\Models\User;
use Simplydigital\CaseManagement\Enums\Permissions\CasePermission;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\StockManagement\Enums\Permissions\StockRequestPermission;
use Simplydigital\StockManagement\Models\StockRequest;

class StockRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(CasePermission::Update->value)
            || $user->can(StockRequestPermission::Manage->value);
    }

    public function view(User $user, StockRequest $stockRequest): bool
    {
        return $user->can(CasePermission::Update->value)
            || $user->can(StockRequestPermission::Manage->value);
    }

    public function create(User $user, tickets $case): bool
    {
        return $case->closed_datetime === null
            && $user->can(CasePermission::Update->value);
    }

    public function manageAny(User $user): bool
    {
        return $user->can(StockRequestPermission::Manage->value);
    }

    public function update(User $user, StockRequest $stockRequest): bool
    {
        return $user->can(StockRequestPermission::Manage->value);
    }
}
