<?php

namespace Simplydigital\CaseManagement\Livewire\Portal;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Livewire\Portal\Concerns\InteractsWithPortalCases;

class ViewCases extends Component
{
    use InteractsWithPortalCases;
    use WithPagination;

    public string $statusFilter = 'open';

    public string $search = '';

    public function updatedStatusFilter(): void
    {
        abort_unless(in_array($this->statusFilter, ['open', 'closed', 'all'], true), 422);

        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        abort_unless(in_array($this->statusFilter, ['open', 'closed', 'all'], true), 422);

        $cases = $this->portalCasesQuery()
            ->when($this->statusFilter !== 'all', function (Builder $query): void {
                $states = $this->statusFilter === 'open'
                    ? CaseState::openStates()
                    : CaseState::closedStates();

                $query->whereIn(
                    'state',
                    array_map(static fn (CaseState $state): string => $state->value, $states)
                );
            })
            ->when(trim($this->search) !== '', function (Builder $query): void {
                $term = '%'.trim($this->search).'%';

                $query->where(function (Builder $query) use ($term): void {
                    $query->where('short_description', 'like', $term)
                        ->orWhere('number', 'like', $term);
                });
            })
            ->latest('updated_at')
            ->latest('id')
            ->paginate(10);

        return view('casemanagement::livewire.portal.view-cases', [
            'cases' => $cases,
        ])->layout('casemanagement::layouts.portal');
    }
}
