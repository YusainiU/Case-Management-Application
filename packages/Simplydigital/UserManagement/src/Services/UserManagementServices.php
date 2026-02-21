<?php

Namespace Simplydigital\UserManagement\Services;

use Simplydigital\UserManagement\Model\UsersDetails;
use Simplydigital\UserMmanagement\Model\UsersStatuses;
use App\Models\User;

class UserManagementServices
{
    // Service class for User Management functionalities
    public function checkUserDetails($userId)
    {
        //return UsersDetails::where('user_id', $userId)->first();
        $u = UsersDetails::where('user_id', $userId)->first();
        if (empty($u)) {
            return $this->createDefaultUserDetails($userId);
        }
        return $u;
    }

    public function createDefaultUserDetails($userId)
    {
        $input = [
            'user_id' => $userId,
            'address_1' => 'Unavailable',
        ];
        return UsersDetails::create($input);
    }
}
