<?php

namespace Tests\Unit;

use Tests\TestCase;
use Simplydigital\CaseManagement\Services\CaseRetrieveTicketStatus;
use Simplydigital\CaseManagement\Services\CaseTicketUtilityServices;
use Simplydigital\CaseManagement\Models\tickets;

class CaseStatusTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_caseStatus(): void
    {
        $service = new CaseRetrieveTicketStatus;
        $util = new CaseTicketUtilityServices;
        //$tickets = tickets::query()->get();
        //$status = $service->retrieveStatuses($tickets);
        $status = $util->retrieveTicketStatuses();
        dump($status);
        dump(collect($status)->sum('noUpdate'));
        $this->assertIsArray($status);
        $this->assertIsInt(collect($status)->sum('taskBreached'));
    }
}
