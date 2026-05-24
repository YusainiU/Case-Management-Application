<?php

namespace Simplydigital\CaseManagement\Repositories;

use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Models\TaskLogs;
use Simplydigital\CaseManagement\Models\slaTask;

class CaseRepository
{

    public function createNewTicket(array $data): tickets
    {
        return tickets::create($data);
    }

    public function createTaskLog(array $data)
    {
        return TaskLogs::create($data);
    }

    public function createSlaTask(array $data)
    {
        return slaTask::create($data);
    }
}



