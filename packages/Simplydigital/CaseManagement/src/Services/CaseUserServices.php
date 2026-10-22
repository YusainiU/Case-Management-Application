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
        $result = UserManagementServices::getAllInternalUsers()->pluck('user_name','user_id')->toArray();
        return $result;
    }

    public function getUserById(int $id)
    {
        return  UserManagementServices::getUserById($id);
    }

    public function getAllCustomerContacts(): array
    {
        return UserManagementServices::getAllCustomerContacts();
    }

    public static function getUserDetails(int $userId)
    {
        return UserManagementServices::getUserDetails($userId);
    }

}