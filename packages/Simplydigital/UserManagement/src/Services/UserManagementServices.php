<?php

namespace Simplydigital\UserManagement\Services;

use Simplydigital\UserManagement\Model\UsersDetails;
use Illuminate\Support\Str;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserManagementServices
{


    // Service class for User Management functionalities

    public function resetUserPassword(User $user, $newPassword)
    {

        $user->forcefill([
            'password' => bcrypt($newPassword)
        ])->save();
    }

    public function generateRandomPassword()
    {
        $password = Str::password(
            length: 12,
            letters: true,
            numbers: true,
            symbols: true
        );
        return $password;
    }


    public function checkUserDetails($userId)
    {
        //return UsersDetails::where('user_id', $userId)->first();
        $u = UsersDetails::where('user_id', $userId)->first();
        if (empty($u)) {
            return $this->createDefaultUserDetails($userId);
        }
        return $u;
    }

    public function createUser($input)
    {
        $user = User::create($input);
        $this->createDefaultUserDetails($user->id);
        return $user;
    }

    public function createUserOnly($input)
    {
        return User::create($input);
    }

    public function createUserDetails($input)
    {
        return UsersDetails::create($input);
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
