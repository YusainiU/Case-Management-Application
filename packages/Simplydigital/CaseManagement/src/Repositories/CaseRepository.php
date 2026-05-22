<?php

namespace Simplydigital\CaseManagement\Repositories;

use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Models\TaskLogs;

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
}



