<?php

Namespace Simplydigital\UserManagement\Livewire;

use LivewireUI\Modal\ModalComponent;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\User;

class UpdateUser extends ModalComponent
{
    public $userId;
    public $name = '';
    public $email = '';
    //public $password = '';
    public $active = true;
    public $modalTitle = 'Update User';
    public $modalAction = 'update';
    public $modalClose = 'close';
    public $roles = [];

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
        $this->roles = [
            ['id' => 1, 'name' => 'Admin'],
            ['id' => 2, 'name' => 'Editor'],
            ['id' => 3, 'name' => 'Viewer'],
        ];
    }

    public function loadUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
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
                Rule::unique(User::class),
            ],
        ])->validate();        
        
        $user = User::find($this->userId);

        if ($user) {
            $user->name = $this->name;
            $user->email = $this->email;
            $user->save();
        }
        $this->closeModal();
        $this->dispatch('userUpdated');

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