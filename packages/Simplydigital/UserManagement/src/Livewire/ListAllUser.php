<?php

Namespace Simplydigital\UserManagement\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
class ListAllUser extends Component
{

    use WithPagination;

    public $userId;
    public $filter = '';
    public $showCreateModal = false;
    public $showUpdateModal = false;
    public $showRoles = false;
    public $showPermissions = false;
    public $showUserProfile = false;
    protected $listeners = [
        'closeRoleModal',
        'closePermissionModal',
        'closeCreateNewUser',
        'userUpdated',
        'closeUserProfile',
    ];    


    public function closeRoleModal()
    {
        $this->closeModal();
    }

    public function closeCreateNewUser()
    {
        $this->closeModal();
    }

    public function closePermissionModal()
    {
        $this->closeModal();
    }

    public function userUpdated()
    {
        $this->closeModal();
    }

    public function closeUserProfile()
    {
        $this->closeModal();
    }    

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
        $this->showPermissions = false;
        $this->showRoles = false;
        $this->showUserProfile = false;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($userId)
    {   
        $user = User::find($userId)->deleteQuietly();
        $this->resetPage();
    }

    public function openCreateUser()
    {
        $this->resetShowModals();
        $this->showCreateModal = true;
    }

    public function openRoles()
    {
        $this->resetShowModals();
        $this->showRoles = true;
    }

    public function openPermissions()
    {
        $this->resetShowModals();
        $this->showPermissions = true;
    }

    public function openUpdateUser($userId)
    {
        $this->resetShowModals();
        $this->userId = $userId;
        $this->showUpdateModal = true;
    }

    public function openUserProfile($userId)
    {
        $this->resetShowModals();
        $this->userId = $userId;
        $this->showUserProfile = true;
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