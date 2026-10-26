<?php

namespace Simplydigital\UserManagement\Livewire;

use App\Models\User;
use Simplydigital\UserManagement\Model\UsersDetails;
use Livewire\WithFileUploads;
use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Facades\Storage;
use Simplydigital\UserManagement\Services\UserManagementServices;

class UserProfile extends ModalComponent
{

    use WithFileUploads;

    public $userId;
    public $user;
    public $userDetails;
    public $userAddress;
    public $name;
    public $email;
    public $phone;
    public $password;
    public $address_1;
    public $address_2;
    public $county;
    public $postcode;
    public $country;
    public $image;
    public $imagePath;
    public $avatar;
    public $identification;
    public $identifications = [];
    public $showEditing = false;
    public $showUpload = false;
    public $showDefault = true;
    public $showResetPassword = false;

    public function mount()
    {
        if ($this->userId) {
            $this->loadUser();
            $this->checkUserDetails();
            $this->generateRandomPassword();
            $this->identifications = config('user-management.identification');  
        }
    }

    public function resetAll()
    {
        $this->loadUser();
        $this->checkUserDetails();
        $this->showDefault = true;
        $this->showUpload = false;
        $this->showEditing = false;
        $this->showResetPassword = false;
        //$this->identification = null;
        $this->image = null;
        $this->imagePath = null;
    }

    public function closeProfile()
    {
        $this->dispatch('close-user-profile');
    }

    public function closeUploadSection()
    {
        $this->resetAll();
    }

    public function generateRandomPassword()
    {
        $service = app(UserManagementServices::class);
        $this->password = $service->generateRandomPassword();
    }

    public function resetUserPassword()
    {
        $services = app(UserManagementServices::class);
        $services->resetUserPassword($this->user, $this->password);
        $this->resetAll();
    }

    public function checkUserDetails()
    {
        $userManagementServices = app(UserManagementServices::class);
        $details = $userManagementServices->checkUserDetails($this->userId);
        $this->userDetails = $details;
        $this->userAddress = $details->getAddress();
        $this->avatar = $details->avatar;
        $this->address_1 = $details->address_1;
        $this->address_2 = $details->address_2;
        $this->county = $details->county;
        $this->postcode = $details->postcode;
        $this->country = $details->country ?? 'United Kingdom';
        $this->userDetails->country = $this->country;
        $this->identification = $details->identification;
        $this->phone = $details->phone ?? '';
    }

    public function loadUser()
    {
        $this->user = User::find($this->userId);
        $this->authorize('update', $this->user);
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->phone = $this->user->phone ?? '';
        $this->checkUserDetails();
    }

    public function resetAndRefresh()
    {
        $this->showUpload = false;
        $this->loadUser();
    }

    public function showUploadSection()
    {
        $this->showDefault = false;
        $this->showUpload = true;
    }

    public function showEditingSection()
    {
        $this->showDefault = false;
        $this->showEditing = true;
    }

    public function showResetPasswordSection()
    {
        $this->showDefault = false;
        $this->showResetPassword = true;
    }

    public function save()
    {
        $this->authorize('update', User::class);

        $profileDisk = config('user-management.PROFILE_IMAGE_DISK');

        $this->validate([
            'image' => 'image|max:2048',
        ]);

        //Check whether image path exists in UserDetails, 
        //if it does delete the file from storage and update path with new image, 
        //if not just save new path to UserDetails

        if ($this->avatar) {
            Storage::disk('private')->delete($this->avatar);
        }; 

        $this->imagePath = $this->image->store($profileDisk, 'private');
        $this->image->delete();
        $this->userDetails->avatar = $this->imagePath;
        $this->userDetails->save();
        $this->resetAll();
    }


    public function removeProfileImage()
    {
        $profileDisk = config('user-management.PROFILE_IMAGE_DISK');

        if ($this->avatar) {
            //Delete path from UserDetails and delete file from storage
            Storage::disk('private')->delete($this->avatar);
            $this->userDetails->avatar = null;
            $this->userDetails->save();
            $this->resetAll();
        }
    }
    
    public function saveDetails()
    {
        $rules = [
            'address_1' => 'nullable|string|max:255',
            'address_2' => 'nullable|string|max:255',
            'county' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $this->userId,
            'identification' => 'nullable|string|max:50',
        ];

        $input = [
            'address_1' => $this->address_1,
            'address_2' => $this->address_2,
            'county' => $this->county,
            'postcode' => $this->postcode,
            'country' => $this->country,
            'phone' => $this->phone,
            'identification' => $this->identification,
        ];

        $profileInput = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        $this->validate($rules);
        $this->userDetails->update($input);
        $this->user->update($profileInput);
        $this->resetAll();
        $this->resetAndRefresh();
    }

    public function render()
    {
        return view('usermanagement::livewire.user-profile');
    }
}
