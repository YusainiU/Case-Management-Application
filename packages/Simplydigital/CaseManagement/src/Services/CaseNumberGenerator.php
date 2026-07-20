<?php

namespace Simplydigital\CaseManagement\Services;

use Simplydigital\CaseManagement\Models\tickets;
use Illuminate\Support\Facades\DB;

class CaseNumberGenerator
{
    public static function generate(): string
    {
        return DB::transaction(function () {

            $year = now()->format('y');
            
            // Lock the latest case for this year
            $lastCase = tickets::where('number', 'like', "CAS-{$year}-%")
                ->orderByDesc('number')
                ->lockForUpdate()
                ->first();

            if (! $lastCase) {
                $next = 1;
            } else {
                // Extract the numeric part
                preg_match('/(\d+)$/', $lastCase->number, $matches);
                $lastNumber = (int) $matches[1];
                $next = $lastNumber + 1;
            }

            return \sprintf('CAS-%s-%06d', $year, $next);
        });
    }

    public static function generateNumber(string $prefix, string $modelName): string
    {
        return DB::transaction(function () use ($prefix, $modelName) {

            $year = now()->format('y');
            
            // Lock the latest case for this year
            $lastCase = $modelName::where('number', 'like', "{$prefix}-{$year}-%")
                ->orderByDesc('number')
                ->lockForUpdate()
                ->first();

            if (! $lastCase) {
                $next = 1;
            } else {
                // Extract the numeric part
                preg_match('/(\d+)$/', $lastCase->number, $matches);
                $lastNumber = (int) $matches[1];
                $next = $lastNumber + 1;
            }

            return \sprintf("$prefix-%s-%06d", $year, $next);
        });
    }    
}