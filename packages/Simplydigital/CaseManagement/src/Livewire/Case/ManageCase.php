<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use Illuminate\Http\UploadedFile;
use Livewire\WithFileUploads;
use LivewireUI\Modal\ModalComponent;
use phpDocumentor\Reflection\Types\Object_;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseTicketServices;
use Simplydigital\CaseManagement\Services\FileUploadService;
use Simplydigital\FileUploadManagement\Exceptions\FileUploadException;
use Simplydigital\FileUploadManagement\Models\UploadedFile as UploadedAttachment;

class ManageCase extends ModalComponent
{
    use WithFileUploads;

    public ?tickets $case = null;

    public ?UploadedFile $attachment = null;
    
    public UploadedAttachment $uploadedAttachment;

    public ?string $message = null;

    public bool $caseClosed = false;

    public array $tabHeaders = [
        'sla' => 'SLA',
        'status' => 'SLA Status',
        'pauses' => 'SLA Pauses',
        'update' => 'Update',
        'progress' => 'Progress',
        'visit' => 'Visit',
        'close' => 'Close & Resolve',
        'tracker' => 'Changes Tracker',
        'uploaded' => 'Upload Files',
        'caseAssistant' => 'Case Assistant (AI)',
    ];

    public string $selectedTab = 'sla';

    /**
     * @var array<string, string>
     */
    protected $listeners = [
        'closeManageSlaModal' => 'closeManageSlaModal',
        'reloadCaseStatus' => '$refresh',
        'caseUpdated' => 'caseUpdated',
    ];

    public function mount()
    {
        $this->authorize('viewAny', $this->case);
        if ($this->case) {
            if ($this->case->closed_datetime) {
                $this->tabHeaders['close'] = 'Reopen Case';
                $this->caseClosed = true;
            }
        }
    }

    public function resetTabs()
    {
        $this->reset([
            'selectedTab',
        ]);
    }

    public function caseUpdated()
    {
        $this->case->refresh();
        $this->message = 'Case has been updated';
    }

    public function resetToastMessage()
    {
        $this->reset([
            'message',
        ]);
    }

    public function closeManageSlaModal()
    {
        $this->resetTabs();
    }

    public function selectTab(string $tabKey)
    {
        if (array_key_exists($tabKey, $this->tabHeaders)) {
            $this->selectedTab = $tabKey;
        }
    }

    public function ticketService()
    {
        return app(CaseTicketServices::class);
    }

    public function closeThisModal()
    {
        $this->dispatch('close-profile-modal');
    }

    public function buildUI()
    {
        $caseNumber = $this->case->number ?? null;
        $title = $caseNumber ? "Manage Case - {$caseNumber}" : 'Manage Case';

        return [
            'title' => $title,
            'description' => $this->case->customer()->first()->name ?? null,
        ];
    }

    public function getAttachmentsProperty()
    {
        if (! $this->case) {
            return collect();
        }

        return app(FileUploadService::class)
            ->files($this->case->number, 'Cases');
    }

    public function removeAttachment(UploadedAttachment $attachment)
    {
        return app(FileUploadService::class)->delete($attachment);
    }

    public function downloadAttachment(UploadedAttachment $attachment)
    {
        return app(FileUploadService::class)->download($attachment);
    }

    public function uploadAttachment(): void
    {

        $this->authorize('create', UploadedAttachment::class);

        $this->validate([
            'attachment' => [
                'required',
                'file',
                'max:10240',
            ],
        ]);

        if (! $this->case) {
            $this->addError(
                'attachment',
                'A case must be selected before uploading a file.'
            );
            return;
        }

        try {
            app(FileUploadService::class)->upload(
                ownerType:'Cases',
                ownerNumber: $this->case->number,
                file: $this->attachment,
                uploadedBy: auth()->id(),
            );

            $this->reset('attachment');

            $this->dispatch('file-uploaded');

            session()->flash(
                'file-upload-success',
                'File uploaded successfully.'
            );
        } catch (FileUploadException $exception) {
            report($exception);
            $this->addError(
                'attachment',
                'The file could not be uploaded.'
            );
        }
    }

    public function render()
    {
        $ui = $this->buildUI();

        return view('casemanagement::livewire.case.manage-case', [
            'ui' => $ui,
        ]);
    }
}
