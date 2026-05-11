<?php

namespace Simplydigital\CaseManagement\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Simplydigital\CaseManagement\Models\slaTask;
use Simplydigital\CaseManagement\Models\TaskSiteVisit;
use Simplydigital\CaseManagement\Models\tickets;
use Illuminate\Database\Eloquent\Collection;

class CaseTicketServices
{
    protected ?Builder $query = null;

    private ?string $sortBy = null;

    private string $sortDirection = 'asc';

    private string|int|null $filter = null;

    private CaseTicketUtilityServices $caseTicketUtilityServices;

    public function __construct()
    {
        $this->query = tickets::query();
        $this->caseTicketUtilityServices = app()->make(CaseTicketUtilityServices::class);
    }

    public function setCustomer(int $customerId)
    {
        $this->query->where('customer_id', $customerId);
    }

    public function getAllOpenCases()
    {
        $this->query->where('closed_datetime', '=', null);
        $this->buildFilter();

        return $this->query;
    }

    public function getOutstandingCases()
    {
        $this->query
            ->where('closed_datetime', '=', null)
            ->where('resolved_datetime','=', null);
        $this->buildFilter();

        return $this->query;          
    }    

    public function getAllClosedCases()
    {
        $this->query->where('closed_datetime', '!=', null);
        $this->buildFilter();

        return $this->query;
    }

    public function getAllOpenResolvedCases()
    {
        $this->query
            ->where('resolved_datetime', '!=', null)
            ->where('closed_datetime', '=', null);
        $this->buildFilter();

        return $this->query;
    }

    public function getAllClosedResolvedCases()
    {
        $this->query
            ->where('resolved_datetime', '!=', null)
            ->where('closed_datetime', '!=', null);
        $this->buildFilter();

        return $this->query;
    }

    public function getCaseById(int $caseId)
    {
        return $this->query->find($caseId);
    }

    public function getCasesByCustomerId(int $customerId)
    {
        $this->query->where('customer_id');
        $this->buildFilter();

        return $this->query;
    }

    public function buildProfileItems(tickets $ticket)
    {
        $items = [];
        foreach ($ticket->getAttributes() as $key => $value) {
            $items[] = [
                'label' => ucfirst(str_replace('_', ' ', $key)),
                'value' => $value,
            ];
        }

        return $items;
    }

    private function buildFilter()
    {
        if ($this->filter) {
            $filterValue = '%'.$this->filter.'%';
            $this->query->where(function ($q) use ($filterValue) {
                $q->orWhere('number', 'like', $filterValue)
                    ->orWhere('short_description', 'like', $filterValue)
                    ->orWhere('description', 'like', $filterValue);
            });
        }

        if ($this->sortBy) {
            $this->query->orderBy($this->sortBy, $this->sortDirection);
        }
    }

    public function sortByField(string $sortBy, ?string $sortDirection = null)
    {
        $this->sortBy = $sortBy;
        if ($sortDirection) {
            $this->sortDirection = $sortDirection;
        }
    }

    public function filterResult(string|int $filter)
    {
        $this->filter = $filter;
    }

    public function createNewTicket(array $data)
    {
        return $this->caseTicketUtilityServices->createNewTicket($data);
    }

    public function reopenTicket(tickets $ticket)
    {
        return $this->caseTicketUtilityServices->reopenTicket($ticket);
    }

    public function closeTicket(tickets $ticket, slaTask $slaTask, array $data)
    {
        return $this->caseTicketUtilityServices->closeTicket($ticket, $slaTask, $data);
    }

    public function retrieveTicketStatus(tickets $ticket)
    {
        return $this->caseTicketUtilityServices->retrieveTicketStatus($ticket);
    }

     public function checkStatusForMultipleTickets(Collection $tickets)
     {
        return $this->caseTicketUtilityServices->checkStatusForMultipleTickets($tickets);
     }

    public function setSiteVisit(tickets $ticket, array $data)
    {
        return $this->caseTicketUtilityServices->setSiteVisit($ticket, $data);
    }
    
    public function updateSiteVisit(TaskSiteVisit $siteVisit, array $data)
    {
        return $this->caseTicketUtilityServices->updateSiteVisit($siteVisit, $data);
    }

    public function setAssignedTo(tickets $ticket, User $assigned_to)
    {
        return $this->caseTicketUtilityServices->setAssignedTo($ticket, $assigned_to);
    }

    public function unassignedUser(tickets $ticket)
    {
        return $this->caseTicketUtilityServices->unassignedUser($ticket);
    }

    public function slaBreachUpdate(slaTask $tasks)
    {
        $this->caseTicketUtilityServices->slaBreachUpdate($tasks);
    }
    

}
