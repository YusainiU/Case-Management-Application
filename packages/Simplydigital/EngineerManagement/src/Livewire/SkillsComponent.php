<?php

namespace Simplydigital\EngineerManagement\Livewire;

use Livewire\Component;
use Simplydigital\EngineerManagement\Models\Skills;
use Livewire\WithPagination;

class SkillsComponent extends Component
{

    use WithPagination;

    
    public $filter = '';
    public $modalMode = ''; // 'add' or 'update'
    public $modalTitle = '';
    public $skillId = null;
    public $showList = false;

    protected $listeners = [
        'skillSaved' => 'loadSkills',
        'closeModal' => 'closeModal',
    ];


    public function mount()
    {
        $this->loadSkills();
    }

    public function loadSkills()
    {
        $skills = Skills::all();
        if($skills) {
            $this->showList = false;
        } else {
            $this->showList = true;
        }
        $this->modalMode = '';
        $this->render();
    }

    public function closeModal()
    {
        $this->modalMode = '';
    }

    public function filter()
    {
        $query = Skills::query();

        if($this->filter)
        {
            $filterValue = "%{$this->filter}%";
            $query->where('skills', 'like', $filterValue)
                  ->orWhere('description', 'like', $filterValue);
        }        
        return $query;
    }

    public function delete($id)
    {
        $skill = Skills::find($id);
        $skill->delete();
        session()->flash('message', 'Skill has been deleted successfully.');
        $this->loadSkills();
    }

    public function toggleAddModal()
    {
        $this->skillId = null;
        $this->modalMode = $this->modalMode ? '' : 'add';
        $this->modalTitle = 'Add Skill';
    }

    public function toggleUpdateModal($id)
    {
        $this->skillId = $id;
        $this->modalMode = $this->modalMode ? '' : 'update';
        $this->modalTitle = 'Update Skill';
    }

    public function render()
    {
        $query = $this->filter();
        $this->showList = $query->exists() ? true : false;
        return view('engineermanagement::livewire.skills-component', [
            'skills' => $query->paginate(10,pageName:'skills-page'),
        ]);
    }
}