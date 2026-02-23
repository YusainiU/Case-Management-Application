<?php
namespace Simplydigital\EngineerManagement\Livewire;

use Simplydigital\EngineerManagement\Models\Skills;
use LivewireUI\Modal\ModalComponent;

class AddUpdateSkillModalComponent extends ModalComponent
{

    public $skillId = null;
    public $name = '';
    public $description = '';
    public $skills;
    public $active = true;
    public $modalTitle = 'Add Skill';
    public $modalAction = 'save';
    public $modalClose = 'close';
    public $showList = false;

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'active' => 'boolean',
        ];
    }

    public function maxWidth(): string
    {
        // Supported: 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl'
        return '6xl';
    }

    public function mount()
    {
        if($this->skillId){
            $this->modalTitle = 'Update Skill';
            $this->loadSkill($this->skillId);            
        }        
    }

    
    public function loadSkill($skillId)
    {
        $this->skills = Skills::find($skillId);
        if($this->skills->exists()){
            $this->name = $this->skills->skills;
            $this->description = $this->skills->description;
        }   
    }

    public function save()
    {
        $this->validate();    
        if ($this->skillId) {
            $skill = Skills::find($this->skillId);
            $skill->update([
                'skills' => $this->name, 
                'description' => $this->description,
                'active' => $this->active,
            ]);
        } else {
            Skills::create([
                'number' => null,
                'skills' => $this->name, 
                'description' => $this->description,
                'active' => $this->active,
            ]);
        }

        $this->dispatch('skillSaved');
    }

    public function close()
    {
        $this->resetForm();
         $this->dispatch('closeModal');
    }

    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->skillId = null;
    }

    public function render()
    {
        return view('engineermanagement::livewire.add-update-skill-modal-component');
    }
}