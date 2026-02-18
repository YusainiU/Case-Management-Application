<?php

Namespace Simplydigital\UserManagement\Livewire;

use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UpdateUser extends ModalComponent
{
    public $userId;
    public $user;
    public $name = '';
    public $email = '';
    //public $password = '';
    public $active = true;
    public $modalTitle = 'Update User';
    public $modalAction = 'update';
    public $modalClose = 'close';
    public $roles = [];
    public $selectedRoles = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email',
        'active' => 'boolean',
    ];

    public function mount($userId)
    {
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
            'name' => $this->name,
            'email' => $this->email,
        ];

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],
            'selectedRoles' => 'array',
        ])->validate();        
        
        $user = User::find($this->userId);

        if ($user) {
            $user->name = $this->name;
            $user->email = $this->email;
            $user->save();

            if(!empty($this->selectedRoles))
            {
                $user->assignRole($this->selectedRoles);
            }
        }

        $this->closeThis();

    }

    public function closeThis()
    {
        $this->dispatch('userUpdated');
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