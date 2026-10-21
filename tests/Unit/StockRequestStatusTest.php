<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Simplydigital\StockManagement\Enums\StockRequestStatus;

class StockRequestStatusTest extends TestCase
{
    public function test_request_status_allows_only_the_next_workflow_stage_or_cancellation(): void
    {
        $this->assertSame(
            [StockRequestStatus::Ordered, StockRequestStatus::Cancelled],
            StockRequestStatus::Requested->nextStatuses(),
        );
        $this->assertTrue(StockRequestStatus::Requested->canTransitionTo(StockRequestStatus::Ordered));
        $this->assertFalse(StockRequestStatus::Requested->canTransitionTo(StockRequestStatus::Dispatched));
        $this->assertTrue(StockRequestStatus::Ordered->canTransitionTo(StockRequestStatus::Dispatched));
        $this->assertTrue(StockRequestStatus::Dispatched->canTransitionTo(StockRequestStatus::Received));
    }

    public function test_received_and_cancelled_requests_are_terminal(): void
    {
        $this->assertSame([], StockRequestStatus::Received->nextStatuses());
        $this->assertSame([], StockRequestStatus::Cancelled->nextStatuses());
        $this->assertFalse(StockRequestStatus::Received->canTransitionTo(StockRequestStatus::Cancelled));
        $this->assertFalse(StockRequestStatus::Cancelled->canTransitionTo(StockRequestStatus::Requested));
    }
}
