<?php

namespace Simplydigital\UserManagement\Livewire;

use App\Models\User;
use Simplydigital\UserManagement\Model\UsersDetails;
use Livewire\WithFileUploads;
use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Facades\Storage;

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
    public $image;
    public $imagePath;
    public $avatar;
    public $isEditing = false;
    public $showUpload = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:20',
        'avatar' => 'nullable|image|max:1024',
    ];

    public function mount()
    {
        if ($this->userId) {
            $this->loadUser();
            $this->checkUserDetails();
        }
    }

    public function checkUserDetails()
    {
        $details = UsersDetails::find($this->userId);
        if(empty($details)){
            $this->createUserDetails();
        }else{
            $this->userDetails = $details;
            $this->userAddress = $details->getAddress();
            $this->avatar = $details->avatar;
        }
    }

    public function createUserDetails()
    {
        $input = [
            'user_id' => $this->userId,
            'address_1' => 'Unavailable',
        ];
        $this->userDetails = UsersDetails::create($input);
    }

    public function loadUser()
    {
        $this->user = User::find($this->userId);
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->phone = $this->user->phone ?? '';
    }

    public function resetAndRefresh()
    {
        $this->showUpload = false;
        $this->loadUser();
    }

    public function showUploadSection()
    {
        $this->showUpload = true;
    }

    public function toggleEdit()
    {
        $this->isEditing = !$this->isEditing;
    }

    public function upload()
    {
        $this->validate([
            'image' => 'image|max:2048',
        ]);

        $this->imagePath = $this->image->store('images', 'private');
    }

    public function updateProfile()
    {
        $this->validate();

        if ($this->image) {
            $this->upload();
        }

        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);

        $this->isEditing = false;
        $this->dispatch('profileUpdated');
    }

    public function render()
    {
        return view('usermanagement::livewire.user-profile');
    }
}
