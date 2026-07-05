<?php

namespace Simplydigital\UserManagement\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Simplydigital\UserManagement\Model\UsersDetails;

class UserManagementServices
{
    // Service class for User Management functionalities

    public function resetUserPassword(User $user, $newPassword)
    {

        $user->forcefill([
            'password' => bcrypt($newPassword),
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
        return $this->createDefaultUserDetails($userId);
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
        $exist = UsersDetails::where('user_id', $userId)->exists();
        if (! $exist) {
            $input = [
                'user_id' => $userId,
                'address_1' => 'Unavailable',
            ];

            return UsersDetails::create($input);
        } else {
            return UsersDetails::where('user_id', $userId)->first();
        }
    }

    public static function getUserById(int $id)
    {
        return UsersDetails::find($id);
    }

    public static function getAllInternalUsers()
    {
        $internals = UsersDetails::query()
            ->join('users', 'users.id', '=', 'users_details.user_id')
            ->whereHas('user.roles', function ($query) {
                $query->where('name', 'Internal User');
            })
            ->select(
                'users_details.*',
                'users.name as user_name'
            )
            ->get();
            
        return $internals;
    }
}
