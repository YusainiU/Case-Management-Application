<?php

namespace Simplydigital\CaseManagement\Services;

class SLALoggerService
{
    private array $logs = [];
    private bool $enabled = false;
    
    public function enable(): void
    {
        $this->enabled = true;
    }
    
    public function disable(): void
    {
        $this->enabled = false;
    }
    
    public function log(string $key, int|string|array $value, ?string $context = null): void
    {
        if (!$this->enabled) {
            return;
        }
        
        if ($context) {
            $this->logs[$context][$key] = $value;
        } else {
            $this->logs[$key] = $value;
        }
    }
    
    public function getLogs(): array
    {
        return $this->logs;
    }
    
    public function clear(): void
    {
        $this->logs = [];
    }
    
    public function getFormattedLogs(): string
    {
        return json_encode($this->logs, JSON_PRETTY_PRINT);
    }
}
