<?php

namespace Tests\Unit;

use Mockery;
//use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use Simplydigital\CaseManagement\Services\CaseTicketUtilityServices;
use Simplydigital\CaseManagement\Services\CaseLogAdministrationService;
use Simplydigital\CaseManagement\Enums\CaseSource;
use Simplydigital\CaseManagement\Enums\CaseCategory;
use Simplydigital\CaseManagement\Enums\CaseState;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\CaseManagement\Repositories\CaseRepository;

use Illuminate\Support\Facades\Auth;

class CaseServiceTest extends TestCase
{
    /**
     * A basic unit test example.
     */

    private function createFakeCaseData(): array
    {
        return [
            'customer_id' => 1,
            'customer_reference' => 'REF123456',
            'category' => CaseCategory::Failure->value,
            'list_of_assets' => [1, 2, 3],
            'source' => CaseSource::Email->value,
            'description' => 'The customer is experiencing technical issues with the product.',
            'short_description' => 'Technical Issue with Product',
            'sla_applications_id' => 1,
            'raised_by_user' => null,
            'raised_by_nonuser' => 'John Doe',
            'contact_telephone' => '123-456-7890',
            'contact_email' => 'john.doe@example.com',
            'created_by' => 6,
            'created_at' => now(),
            'state' => CaseState::Open->value,
            'number' => 'CASE-2024-0001',            
        ];
    }

    public function test_create_case(): void
    {

        $fakeCaseData = $this->createFakeCaseData();

        $repoMock = Mockery::mock(CaseRepository::class);
        $repoMock->shouldReceive('createNewTicket')
            ->once()
            ->andReturn(new tickets($fakeCaseData));
        
        $ServiceUtility = new CaseTicketUtilityServices($repoMock);
        $createdNewCase = $ServiceUtility->createNewTicket($fakeCaseData);
        dump($createdNewCase);

        $this->assertEquals($createdNewCase['customer_id'], $createdNewCase['customer_id']);
        $this->assertEquals($createdNewCase['customer_reference'], $createdNewCase['customer_reference']);
        $this->assertEquals($createdNewCase['category'], $createdNewCase['category']);
        $this->assertEquals($createdNewCase['source'], $createdNewCase['source']);
        $this->assertEquals($createdNewCase['description'], $createdNewCase['description']);
        $this->assertEquals($createdNewCase['short_description'], $createdNewCase['short_description']);
        $this->assertEquals($createdNewCase['sla_applications_id'], $createdNewCase['sla_applications_id']);
        $this->assertEquals($createdNewCase['raised_by_user'], $createdNewCase['raised_by_user']);
        $this->assertEquals($createdNewCase['raised_by_nonuser'], $createdNewCase['raised_by_nonuser']);
        $this->assertEquals($createdNewCase['contact_telephone'], $createdNewCase['contact_telephone']);
        $this->assertEquals($createdNewCase['contact_email'], $createdNewCase['contact_email']);
        $this->assertEquals($createdNewCase['created_by'], $createdNewCase['created_by']);
        $this->assertEquals($createdNewCase['state'], $createdNewCase['state']);
        $this->assertEquals($createdNewCase['ticket_number'], $createdNewCase['ticket_number']);
    
    }

    public function test_create_case_log(): void
    {

        //Create a case first to get the necessary data for the log
        $fakeCaseData = $this->createFakeCaseData();
        $repoMock = Mockery::mock(CaseRepository::class);
        $repoMock->shouldReceive('createNewTicket')
            ->once()
            ->andReturn(new tickets($fakeCaseData));
        $ServiceUtility = new CaseTicketUtilityServices($repoMock);
        $logService = new CaseLogAdministrationService();
        $createdNewCase = $ServiceUtility->createNewTicket($fakeCaseData);        
        $this->assertNotNull($createdNewCase);

        // Now create a log text for the created case
        $logText = $logService->buildTicketCreatedText($createdNewCase);
        dump($logText);
        $this->assertEquals($logText, $logText);

        // Prepare fake log data based on the created case and log text
        $fakeLogdata = [
            'number' => null,
            'name' => $createdNewCase['ticket_number'],
            'short_description' => $createdNewCase['short_description'],
            'type' => config('case-management.logs.type.system'),
            'source' => $createdNewCase['source'],
            'user_id' => $createdNewCase['created_by'],
            'ticket_id' => $createdNewCase['ticket_id'],
            'description' => $logText,
        ];            

        // Mock the CaseRepository to return a TaskLogs instance when createTaskLog is called
        $logMock = Mockery::mock(CaseRepository::class);
        $logMock->shouldReceive('createTaskLog')
            ->once()
            ->andReturn(new TaskLogs($fakeLogdata));
        $logServiceWithMock = new CaseLogAdministrationService($logMock);
        $createdLog = $logServiceWithMock->createSystemLog($createdNewCase, $logText);
        dump($createdLog);
        $this->assertNotNull($createdLog);
        
        
    }
}
