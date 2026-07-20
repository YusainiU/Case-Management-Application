<?php

namespace Simplydigital\CaseManagement\Livewire\Case;

use Livewire\Component;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Services\CaseTicketServices;
use Simplydigital\CaseManagement\Services\CaseUserServices;
class CloseAndResolveCase extends Component
{

    public ?tickets $case = null;
    public string $mode = "resolve";
    public array $form_close  = [];
    public array $form_resolve = [];
    public array $resolutions = [];
    public array $resolvers = [];
    public ?string $message = null;
    public bool $isClosed = false;
    public bool $isResolved = false;

    public function resetAll()
    {
        $this->reset([
            'message',
        ]);
    }

    protected function caseServices(): CaseTicketServices
    {
        return app(CaseTicketServices::class);
    }    
    private function validateResolve()
    {
        $datetime = $this->form_resolve['resolved_datetime'];
        $resolution = $this->form_resolve['resolution'];
        $by = $this->form_resolve['resolved_by'];
        $notes = trim($this->form_resolve['resolution_details']);
        return $datetime && $resolution && $by && $notes;
    }

    private function validateClose()
    {
        return $this->form_close['closed_datetime'] !== null;
    }

    public function mount()
    {
        $this->resyncVars();
        $this->buildOptions();
        $this->loadForms();
    }

    public function resyncVars()
    {
        $this->isResolved = $this->case->is_resolved;
        $this->isClosed = $this->case->is_closed;
    }

    public function close()
    {
        $validated = $this->validateClose();
        $service = $this->caseServices();
        if($validated) {
            $service->closeCase($this->case, $this->form_close);
            $this->message = "Case has been closed";
            $this->case->refresh();
            $this->resyncVars();
        }else{
            $this->message = "Close Datetime is required";
        }
        return;
    }

    public function resolve()
    {
        $this->resetAll();
        $validated = $this->validateResolve();
        $service = $this->caseServices();
        if($validated) {
            $service->resolveCase($this->case, $this->form_resolve);
            $this->message = "The case has been resolved";
            $this->case->refresh();
            $this->resyncVars();
        }else{
            $this->message = "Resolve Datetime, Resolution and Resolved By are all required";
        }
        return;
    }

    public function loadForms()
    {
        $this->form_resolve = [
          'resolution'  => $this->case?->resolution ?? null,
          'resolved_by' => $this->case?->resolved_by ?? null,
          'resolved_datetime' => $this->case?->resolvedDateTime(),
          'resolution_details' => $this->case?->resolution_details,
        ];

        $this->form_close = [
            'closed_datetime' => $this->case?->closedDateTime(),
        ];
        
    }

    private function buildOptions()
    {
        //Resolutions
        $resol = Config('case-management.ticketManagement.resolution');
        ksort($resol);
        $this->resolutions = $resol;

        //Resolved By
        $this->resolvers = CaseUserServices::getAllInternalUserIds();        
        
    }

    public function resetToastMessage()
    {
        $this->reset([
            'message',
        ]);
    }
    
    public function save()
    {
        if(!$this->isResolved) {
            $this->resolve();
        }else{
            if(!$this->isClosed) {
                $this->close();
            }
        }
        return;
    }

    public function reopen()
    {
        $newCase = $this->caseServices()->reopenTicket($this->case);
        if($newCase){
            $this->case = $newCase;
            $this->case->refresh();
            $this->message = "Reopened, new case is created";
        }
        return;
    }

    public function render()
    {
        return view('casemanagement::livewire.case.close-and-resolve-case');
    }


} 