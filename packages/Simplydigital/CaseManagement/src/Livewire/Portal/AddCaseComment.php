<?php

namespace Simplydigital\CaseManagement\Livewire\Portal;

use Illuminate\View\View;
use Livewire\Component;
use Simplydigital\CaseManagement\Livewire\Portal\Concerns\InteractsWithPortalCases;
use Simplydigital\CaseManagement\Models\TaskLogs;

class AddCaseComment extends Component
{
    use InteractsWithPortalCases;

    public int $caseId;

    public string $comment = '';

    public function mount(int $caseId): void
    {
        $this->caseId = $caseId;
        $this->findPortalCase($this->caseId);
    }

    public function submit(): void
    {
        $case = $this->findPortalCase($this->caseId);
        $this->authorize('portal.comment-case', $case);

        $validated = $this->validate([
            'comment' => ['required', 'string', 'max:10000'],
        ]);
        $user = $this->portalUser();

        TaskLogs::query()->create([
            'name' => $case->number,
            'short_description' => $case->short_description,
            'type' => config('case-management.logs.type.external_update'),
            'source' => config('case-management.logs.source.external'),
            'user_id' => $user->id,
            'external_user' => $user->name,
            'ticket_id' => $case->id,
            'description' => $validated['comment'],
        ]);

        $this->reset('comment');
    }

    public function render(): View
    {
        $case = $this->findPortalCase($this->caseId);

        return view('casemanagement::livewire.portal.add-case-comment', [
            'case' => $case,
            'comments' => $case->taskLogs()
                ->with('user')
                ->where('source', config('case-management.logs.source.external'))
                ->where('type', config('case-management.logs.type.external_update'))
                ->oldest('created_at')
                ->oldest('id')
                ->get(),
        ]);
    }
}
