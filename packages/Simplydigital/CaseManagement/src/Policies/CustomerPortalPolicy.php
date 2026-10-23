<?php

namespace Simplydigital\CaseManagement\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Enums\Permissions\CustomerPortalPermission;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\CustomerContacts;
use Simplydigital\CaseManagement\Models\tickets;
class CustomerPortalPolicy
{
    public function accessPortal(User $user): bool
    {
        return $this->accessibleContacts($user)->exists();
    }

    public function viewCase(User $user, tickets $case): bool
    {
        return $this->accessibleContacts($user)
            ->where('customer_id', $case->customer_id)
            ->exists();
    }

    public function createCase(User $user, Customer $customer): bool
    {
        return $this->accessibleContacts($user)
            ->where('customer_id', $customer->id)
            ->exists();
    }

    public function commentOnCase(User $user, tickets $case): bool
    {
        return $this->viewCase($user, $case)
            && $case->closed_datetime === null
            && CaseState::isOpen($case->state->value);
    }

    /**
     * @return Collection<int, int>
     */
    public function accessibleCustomerIds(User $user): Collection
    {
        return $this->accessibleContacts($user)->pluck('customer_id');
    }

    /**
     * @return HasMany<CustomerContacts, User>
     */
    private function accessibleContacts(User $user): HasMany
    {
        return $user->customerContacts()
            ->where('active', true)
            ->where('has_portal_access', true)
            ->whereHas('customer', function (Builder $query): void {
                $query->where('active', true)
                    ->where('portal_enabled', true);
            });
    }

}
