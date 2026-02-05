<?php

Namespace Simplydigital\UserManagement\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
class ListAllUser extends Component
{

    use WithPagination;

    public $userId;
    public $filter = '';
    public $showCreateModal = false;
    public $showUpdateModal = false;
    protected $listeners = [
        'closeModal',
    ];    


    public function closeModal()
    {
        $this->resetShowModals();
        $this->resetPage();
    }

    public function resetShowModals()
    {
        $this->userId = null;
        $this->showCreateModal = false;
        $this->showUpdateModal = false;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateUser()
    {
        $this->resetShowModals();
        $this->showCreateModal = true;
    }

    public function openUpdateUser($userId)
    {
        $this->resetShowModals();
        $this->userId = $userId;
        $this->showUpdateModal = true;
    }

    public function filter()
    {

        $query = User::query();
        $query->where('name', 'like', '%' . $this->filter . '%')
              ->orWhere('email', 'like', '%' . $this->filter . '%');
        return $query;
    }

    public function render()
    {
        $users = $this->filter();
        $showList = $users->exists() ? true : false;
        if($showList){
            $users = $users->paginate(10);
        }else{
            $users = [];
        }

        return view('usermanagement::livewire.list-all-user', [
            'users' => $users,
            'showList' => $showList,
        ]);
    }
}       