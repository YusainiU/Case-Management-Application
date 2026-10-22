<?php

namespace Simplydigital\CaseManagement\Livewire\Portal\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Policies\CustomerPortalPolicy;
use Simplydigital\CaseManagement\Services\CaseCustomerServices;

trait InteractsWithPortalCases
{
    protected function portalUser(): User
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        return $user;
    }

    protected function portalCustomer()
    {
        $user = auth()->user();
        $userDetails = CaseCustomerServices::getCustomerContactByUserId($user->id);
        return ($userDetails && $userDetails->customer_id) ? $userDetails->customer_id : null;
        
    }

    protected function portalCasesQuery(): Builder
    {
        $user = $this->portalUser();
        $this->authorize('portal.access');

        return tickets::query()->whereIn(
            'customer_id',
            app(CustomerPortalPolicy::class)->accessibleCustomerIds($user)
        );
    }

    protected function findPortalCase(int $caseId): tickets
    {
        $case = $this->portalCasesQuery()->findOrFail($caseId);
        $this->authorize('portal.view-case', $case);

        return $case;
    }
}
