<?php

Namespace Simplydigital\UserManagement\Livewire;

use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Spatie\Permission\Models\Role;


class UpdateUser extends ModalComponent
{
    public $userId;
    public $user;
    public $active = true;
    public $modalTitle = 'Update User';
    public $modalAction = 'update';
    public $modalClose = 'close';
    public $name;
    public $email;
    public $roles = [];
    public $selectedRoles = [];

    public function mount($userId)
    {
        $this->authorize('update', User::class);
        $this->userId = $userId;
        $this->loadUser($this->userId);
        $this->loadRoles();
    }

    public function loadRoles()
    {
        $this->roles = Role::all()->pluck('name', 'name');
        if(!empty($this->user)) {
            $roles = $this->user->getRoleNames()->toArray();
            if(!empty($roles)) {
                $r = array_values($roles);
                $this->selectedRoles = $r;
            }
        }
    }

    public function loadUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $this->user = $user;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->active = $user->active;
        }
    }

    public function confirm()
    {

        $input = [
            'selectedRoles' => $this->selectedRoles,
        ];
        
        Validator::make($input, [
            'selectedRoles' => 'array',
        ])->validate();        

        $user = User::find($this->userId);
        if ($user) {
            if(!empty($this->selectedRoles))
            {                
                $user->syncRoles($this->selectedRoles);
            }
        }

        $this->closeThis();

    }

    public function closeThis()
    {
        $this->dispatch('user-updated');
        $this->closeModal();
    }

    public function maxWidth(): string
    {
        return '6xl';
    }

    public function render()
    {
        return view('usermanagement::livewire.update-user');
    }


}   