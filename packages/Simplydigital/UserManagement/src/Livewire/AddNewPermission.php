<?php

namespace Simplydigital\UserManagement\Livewire;

use Livewire\WithPagination;
use LivewireUI\Modal\ModalComponent;
use Simplydigital\UserManagement\Services\PermissionRegistry;
use Simplydigital\UserManagement\Services\PermissionSynchronizer;
use Spatie\Permission\Models\Permission;

class AddNewPermission extends ModalComponent
{
    use WithPagination;

    public $showModal = true;

    public int $perPage = 5;

    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeForm()
    {
        $this->showModal = false;
        $this->dispatch('close-permission-modal');
    }

    public static function modalMaxWidth(): string
    {
        return '6xl';
    }

    public function confirm(PermissionSynchronizer $synchronizer): void
    {
        $synchronizer->sync();
        $this->dispatch('permissions-synchronised');
        $this->dispatch('permission-added');
        sleep(3);
    }

    public function render()
    {
        $guard = config('auth.defaults.guard', 'web');
        $databasePermissions = Permission::query()
            ->where('guard_name', $guard)
            ->pluck('name')
            ->flip();        
        $registryPermissions = collect(app(PermissionRegistry::class)->all());

        /*
         * Compare package definitions with database.
         */
        $allPermissions = $registryPermissions->map(function ($definition) use ($databasePermissions) {
            return [
                'name' => $definition->name,
                'description' => $definition->description,
                'group' => $definition->group,
                'guard' => $definition->guard,

                'registered' => true,

                'database' => $databasePermissions->has(
                    $definition->name
                ),
            ];
        });        


        $total = $allPermissions->count();
        $lastPage = max(1, (int) ceil($total / $this->perPage));
        $currentPage = $this->getPage();

        // Prevent being on a page that no longer exists.
        if ($currentPage > $lastPage) {
            $this->setPage($lastPage);
            $currentPage = $lastPage;
        }

        $permissions = $allPermissions->forPage($currentPage, $this->perPage)->values();

        return view('usermanagement::livewire.add-new-permission', [
            'permissions' => $permissions,
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'total' => $total,
        ]);

    }
}
