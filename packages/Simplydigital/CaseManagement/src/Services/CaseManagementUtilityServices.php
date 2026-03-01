<?php

namespace Simplydigital\CaseManagement\Services;

class CaseManagementUtilityServices
{
    public static function generateRecordNumber($prefix)
    {
        return $prefix . '-' . now()->format('YmdHis') . mt_rand(1000, 9999);
    }
}