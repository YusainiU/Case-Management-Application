<?php

Namespace Simplydigital\UserManagement\Livewire;

use LivewireUI\Modal\ModalComponent;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User;

class AddNewUser extends ModalComponent
{

    public $name;
    public $email;
    public $password;

    public function mount()
    {
        $this->generateRandomPassword();
    }

    public function maxWidth(): string
    {
        return '6xl';
    }

    public function generateRandomPassword()
    {
        $this->password = Str::password(
            length: 12,
            letters: true,
            numbers: true,
            symbols: true
        );
    }

    public function confirm()
    {

        $input = [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
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
            'password' => [
                'required',
                'string',
                Password::min(8)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],            
        ])->validate();    

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
            'active' => true,
        ]);

        session()->flash('message', 'User added successfully.');

        $this->closeThis();
        
    }

    public function closeThis()
    {
        $this->reset();
        $this->dispatch('closeCreateNewUser');
    }    

    public function render()
    {
        return view('usermanagement::livewire.add-new-user');
    }
}   