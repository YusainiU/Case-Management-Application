<?php

Namespace Simplydigital\EngineerManagement\Services;
use Illuminate\Support\Facades\DB;

use Simplydigital\EngineerManagement\Models\EngineerManagement;
use Simplydigital\EngineerManagement\Models\Skills;
use Simplydigital\EngineerManagement\Models\Certificates;
use App\Models\User;
use Simplydigital\EngineerManagement\Models\EngineerCertifications;

class EngineerManagementServices
{
    // Service class for Engineer Management functionalities
    public function getAllEngineers()
    {
        return EngineerManagement::all();
    }

    public function getAllCertificates()
    {
        return Certificates::all();
    }

    public function getAllSkills()
    {
        return Skills::all();
    }

    public function getEngineersBySkillId($skillId)
    {
        return EngineerManagement::with('skills')
            ->whereHas('skills', function ($query) use ($skillId) {
                $query->where('active', true)
                      ->where('id', $skillId);
            })
            ->get();
    }
    
    public function getEngineersByCertificationId($certificationId)
    {
        return EngineerManagement::with('certifications')
            ->whereHas('certifications', function ($query)  use ($certificationId) {
                $query->where('active', true)
                      ->where('id', $certificationId);
            })
            ->get();
    }
    
    public function getTotalExpiredCertificationsCount()
    {
        $expired = $this->getExpiredEngineerCertifications();
        return $expired->count();
    }
    public function getExpiredEngineerCertifications()
    {
        $currentDate = now();
        return EngineerCertifications::where('expiry_date', '<', $currentDate)->get();
    }

    public function getEngineerActiveSkills($engineerId)
    {
        $skills = EngineerManagement::where('id', $engineerId)
            ->with('skills')
            ->whereHas('skills', function ($query) {
                $query->where('active', true);
            })
            ->get()->first();
            return $skills ? $skills->skills : collect();
    }
    
    public function getEngineerActiveCertifications($engineerId)
    {
        $cert =  EngineerManagement::where('id', $engineerId)
            ->with('certifications')
            ->whereHas('certifications', function ($query) {
                $query->where('active', true);
            })
            ->get()->first();
        return $cert ? $cert->certifications : collect();
    }
    
    public function getNonEngineeringUsers()
    {
        return DB::table('users')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('engineer_management')
                    ->whereColumn('engineer_management.user_id', 'users.id');
            })
            ->pluck('name', 'id');
    }
    
    public function getEngineerManagers()
    {
        return DB::table('users')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('engineer_management')
                    ->whereColumn('engineer_management.user_id', 'users.id')
                    ->where('engineer_management.position', 'manager')
                    ->where('engineer_management.active', true);
            })
            ->pluck('name', 'id');
    }
    
    public function makeUserAnEngineer(
        User $user,
        User $manager,
        string $position = '',
        string $department = '',
        bool $active = true,
    )
    {
        return EngineerManagement::create([
            'user_id' => $user->id,
            'manager_id' => $manager ? $manager->id : null,
            'position' => $position ?? 'engineer',
            'department' => $department ?? 'general',
            'active' => $active,
        ]);    
    }

    public function removeEngineerByUserId(User $user)
    {
        if(EngineerManagement::firstWhere('user_id', $user->id) !== null){
            return EngineerManagement::where('user_id', $user->id)->delete();
        }
        return false;
    }

}