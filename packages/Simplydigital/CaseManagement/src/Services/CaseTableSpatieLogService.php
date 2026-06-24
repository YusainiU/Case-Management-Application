<?php

namespace Simplydigital\CaseManagement\Services;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\slaApplication;
use Simplydigital\CaseManagement\Models\Asset;

class CaseTableSpatieLogService
{

    protected array $lookupFields = [
        'customer_id' => Customer::class,
        'sla_application_id' => slaApplication::class,
        'created_by' => User::class,
        'list_of_assets' => Asset::class,
        'raised_by_user' => User::class,
        'assigned_to' => User::class,
        'closed_ticket_id' => tickets::class,
    ];

    protected function formatValue(string $field, mixed $value): mixed
    {

        if (! isset($this->lookupFields[$field]) || ! $value) {
            return $value;
        }
        
        $model = $this->lookupFields[$field];
        $fvalue = \is_array($value) ? $value[$field] : $value ;
        
        if($field === 'list_of_assets') {
            if(\is_array($fvalue)){
                return $model::whereIn('id', $fvalue)->pluck('short_description')->implode(', ');
            }
        }

        if($model === User::class) {
            return $model::find($fvalue)?->name ?? $fvalue;
        }

        if($model === tickets::class) {
            return $model::find($fvalue)?->number ?? $fvalue;
        }

        return $fvalue;
    }


    /*
        To use ,,
            $this->tableActivities($ticket)->get();
            $this->tableActivities($user)->paginate(50);
    */
    public function tableActivities(Model $model)
    {
        
        return $model->activities()
            ->with('causer')
            ->latest();
          
    }

    /*
        To use ..       
        $activities = $this->tableActivities($ticket)->get();
        $changes = $this->flattenActivities($activities);        
    */

    public function flattenActivities(iterable $activities)
    {
        $changes = collect();
        $updated = [];

        foreach ($activities as $activity) {
                    
            $newValues = $activity->attribute_changes->get('attributes', []);
            $oldValues = $activity->attribute_changes->get('old', []);
                        
            foreach ($newValues as $field => $newValue) {

                if(empty($oldValues)) continue;

                if($oldValues[$field] == $newValue){
                    continue;
                }

                if($field == 'updated_at'){
                    $updated[]=['oldValues' => $oldValues[$field],'newValue' => $newValue,];
                }

                $changes->push([
                    'date' => $activity->created_at->format('d-m-Y H:i'),
                    'user' => $activity->causer?->name,
                    'event' => $activity->event,
                    'field' => $field,
                    'old' => $this->formatValue($field, $oldValues[$field]),
                    'new' => $this->formatValue($field, $newValues),
                ]);                
            }

        }
                
        return $changes;
    }


}
