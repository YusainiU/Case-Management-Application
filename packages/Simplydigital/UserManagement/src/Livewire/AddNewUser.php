<?php

Namespace Simplydigital\UserManagement\Livewire;

use LivewireUI\Modal\ModalComponent;
use App\Models\User;

class AddNewUser extends ModalComponent
{
    public $name;
    public $email;
    public $password;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8',
    ];

    public function maxWidth(): string
    {
        return '6xl';
    }

    public function addUser()
    {
        $this->validate();

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
        ]);

        session()->flash('message', 'User added successfully.');

        // Reset form fields
        $this->reset(['name', 'email', 'password']);

        // Emit event to refresh the user list        $this->emit('closeModal');
        $this->dispatch('closeModal');
        
    }

    public function render()
    {
        return view('usermanagement::livewire.add-new-user');
    }
}   