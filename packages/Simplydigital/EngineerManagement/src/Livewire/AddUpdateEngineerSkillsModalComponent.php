<?php

namespace Simplydigital\EngineerManagement\Livewire;

use LivewireUI\Modal\ModalComponent;
use Simplydigital\EngineerManagement\Models\Skills;
use Simplydigital\EngineerManagement\Models\EngineerSkills;
use Simplydigital\EngineerManagement\Models\EngineerManagement;

class AddUpdateEngineerSkillsModalComponent extends ModalComponent
{

    public $engineerId;
    public $engineerSkillId;
    public $engineerSkills;
    public $currentSkill;
    public $engineer;
    public $modalMode='closedAndRefresh'; // 'add' or 'update'
    public $modalTitle='Engineer Skills';
    public $modalSave = 'save';
    public $skills = [];
    public $skill_levels = [];
    public $displayList = true;

    public $skill_id;
    public $skill_level;
    public $description ;
    public $active = true;
    public $originExternalForUpdate = false;

    public static function maxWidth(): string
    {
        // Supported: 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl', '7xl'
        return '6xl';
    }

    public function mount()
    {
        if($this->originExternalForUpdate){
            $this->updateSkill($this->engineerSkillId);
        }else{
            $this->initialise();
        }
    }

    public function initialise()
    {
        $this->engineer = $this->getEngineer();
        $this->engineerSkills = $this->engineer->skills;
        $this->skills = $this->getAllActiveSkills();
        $this->skill_levels = config('engineer-management.skill_levels');

        if($this->engineerSkills->isEmpty()){
            $this->displayList = false;
        }

    }

    public function rules()
    {
        return [
            'skill_id' => 'required|integer',
            'skill_level' => 'required|string',
            'description' => 'nullable',
        ];
    }
    
    public function getAllActiveSkills()
    {
        return Skills::where('active','=',true)->pluck('skills','id');
    }

    public function getEngineer()
    {
        // Logic to retrieve engineer based on $engineerId
        return EngineerManagement::find($this->engineerId);
    }

    public function retrieveEngineerSkills()
    {
        // Logic to retrieve engineer skills based on $engineerId
        $skills = EngineerSkills::where('engineer_id', $this->engineerId)->get();
        return $skills;
    }

    public function getSkill($skillId)
    {
        $this->modalSave = 'update';
        $this->currentSkill = EngineerSkills::find($skillId);
        $this->skill_id = $this->currentSkill->skill_id;
        $this->skill_level = $this->currentSkill->skill_level;
        $this->description = $this->currentSkill->description;
        $this->active = $this->currentSkill->active;
    }

    public function update()
    {
        $this->validate();
        $input = [
            'skill_id' => $this->skill_id,
            'skill_level' => $this->skill_level,
            'description' => $this->description,
            'active' => $this->active,
        ];

        $this->currentSkill->update($input);
        $this->resetForm();
        
        if($this->originExternalForUpdate) {
            $this->closedAndRefresh();
        }else{
            session()->flash('message', 'The skill has been updated');    
            $this->displayList = true;
            $this->initialise();
        }

    }

    public function save()
    {
        $this->validate();
        $input = [
            'number' => null,
            'engineer_id' => $this->engineerId, 
            'skill_id' => $this->skill_id,
            'skill_level' => $this->skill_level,
            'description' => $this->description,
        ];
        EngineerSkills::create($input);
        session()->flash('message', 'Skill has been added to the engineer profile');
        $this->resetForm();
        $this->closedAndRefresh();
    }

    public function resetForm()
    {
        $this->skill_id = '';
        $this->skill_level = '';
        $this->description = '';
        $this->active = true;
    }

    public function backToList()
    {
        $this->displayList = true;
    }

    public function closedAndRefresh()
    {
        $this->dispatch('closedAddUpdateEngineerSkillModalComponent');
        $this->closeModal();
    }

    public function createNewSkill()
    {
        $this->modalSave = 'save';
        $this->displayList = false;
    }

    public function updateSkill($engineerSkillId)
    {
        $this->resetForm();
        $this->getSkill($engineerSkillId);
        $this->initialise();
        $this->displayList = false;
        $this->modalSave = 'update';
    }
        
    public function render()
    {
        return view('engineermanagement::livewire.add-update-engineer-skills-modal-component');
    }
}