<?php

namespace Simplydigital\CaseManagement\Services;

use Simplydigital\UserManagement\Services\UserManagementServices;

class CaseUserServices
{
    // Service class for Case User related functionalities
    private UserManagementServices $userManagementServices;

    public function __construct(UserManagementServices $userManagementServices)
    {
        $this->userManagementServices = $userManagementServices;
    }

    public static function getAllInternalUsers()
    {
        return UserManagementServices::getAllInternalUsers();
    }

    public static function getAllInternalUserIds(): array
    {
        return UserManagementServices::getAllInternalUsers()->pluck('user_name','id')->toArray();
    }

}