<?php

namespace Simplydigital\CaseManagement\Livewire\Portal;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseSource;
use Simplydigital\CaseManagement\Livewire\Portal\Concerns\InteractsWithPortalCases;
use Simplydigital\CaseManagement\Models\Asset;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\slaApplication;
use Simplydigital\CaseManagement\Policies\CustomerPortalPolicy;
use Simplydigital\CaseManagement\Services\CaseTicketServices;
use Simplydigital\CaseManagement\Services\UI;

class CreateCase extends Component
{
    use InteractsWithPortalCases;

    public ?int $customerId = null;

    /**
     * @var array<int, int|string>
     */
    public array $assetIds = [];

    public string $category = '';

    public string $subject = '';

    public string $description = '';

    public ?string $customerReference = null;

    protected function rules(): array
    {
        return [
            'customerId' => ['required', 'integer'],
            'assetIds' => ['required', 'array', 'min:1'],
            'assetIds.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('assets', 'id')
                    ->where('customer_id', $this->customerId)
                    ->where('active', true),
            ],
            'category' => ['required', 'string', 'in:'.implode(',', array_keys(CaseCategory::options()))],
            'subject' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:10000'],
        ];
    }

    public function mount()
    {
        $customer = $this->portalCustomer();
        $customer ? $this->customerId = $customer : null;
    }

    public function updatedCustomerId(): void
    {
        $this->assetIds = [];
        $this->resetValidation('assetIds');
    }

    public function submit(CaseTicketServices $caseTicketServices): ?RedirectResponse
    {
        $validated = $this->validate();
        $user = $this->portalUser();
        $customer = Customer::query()
            ->whereKey($validated['customerId'])
            ->whereIn(
                'id',
                app(CustomerPortalPolicy::class)
                    ->accessibleCustomerIds($user)
            )
            ->firstOrFail();

        $this->authorize('portal.create-case', $customer);

        $contact = $user->customerContacts()
            ->with('customerUserDetails')
            ->where('customer_id', $customer->id)
            ->where('active', true)
            ->where('has_portal_access', true)
            ->whereHas('customer', function (Builder $query): void {
                $query->where('active', true)
                    ->where('portal_enabled', true);
            })
            ->firstOrFail();

        $slaApplication = slaApplication::query()
            ->where('customer_id', $customer->id)
            ->where('active', true)
            ->whereHas('serviceLevelAgreement')
            ->oldest('id')
            ->first();

        if (! $slaApplication) {
            $this->addError(
                'customerId',
                'This customer does not have an active service level agreement. Please contact support.'
            );

            return null;
        }

        $ticket = $caseTicketServices->createNewTicket([
            'customer_id' => $customer->id,
            'sla_applications_id' => $slaApplication->id,
            'category' => $validated['category'],
            'source' => CaseSource::Portal->value,
            'short_description' => $validated['subject'],
            'description' => $validated['description'],
            'raised_by_user' => $contact->user_id,
            'raised_by_nonuser' => null,
            'contact_telephone' => $contact->customerUserDetails?->phone ?? '',
            'contact_email' => $user->email,
            'list_of_assets' => array_map('intval', $validated['assetIds']),
            'customer_reference' => $this->customerReference,
        ]);

        return redirect()->route('portal.cases.show', ['case' => $ticket->id]);
    }

    public function render(): View
    {
        $this->authorize('portal.access');
        $user = $this->portalUser();
        $customerIds = app(CustomerPortalPolicy::class)
            ->accessibleCustomerIds($user);
        $selectedCustomerIsAccessible = $this->customerId !== null
            && $customerIds->contains($this->customerId);
        $assets = $selectedCustomerIsAccessible
            ? Asset::query()
                ->where('customer_id', $this->customerId)
                ->where('active', true)
                ->orderBy('short_description')
                ->get()
            : collect();
        $assetOptions = UI::createArrayforMultiselect(
            $assets->mapWithKeys(fn (Asset $asset): array => [
                $asset->id => $asset->number.' - '.$asset->short_description,
            ])->all()
        );

        return view('casemanagement::livewire.portal.create-case', [
            'customers' => Customer::query()
                ->whereIn('id', $customerIds)
                ->where('active', true)
                ->where('portal_enabled', true)
                ->orderBy('name')
                ->get(),
            'assetOptions' => $assetOptions,
            'hasAvailableAssets' => $assets->isNotEmpty(),
            'categories' => CaseCategory::options(),
        ])->layout('casemanagement::layouts.portal');
    }
}
