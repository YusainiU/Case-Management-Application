<?php

namespace Simplydigital\CaseManagement\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Simplydigital\CaseManagement\Models\Asset;
use Simplydigital\CaseManagement\Models\Customer;
use Simplydigital\CaseManagement\Models\slaApplication;
use Simplydigital\CaseManagement\Models\tickets;
use Carbon\Carbon;

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
        'updated_at' => Carbon::class,
        'ticket_id' => tickets::class,
        'respond_by' => Carbon::class,
        'fix_by' => Carbon::class,
        'responded_at' => Carbon::class,
        'fixed_at' => Carbon::class,
        'breached_at' => Carbon::class,
        'deleted_at' => Carbon::class,
    ];

    protected function formatValue(string $field, mixed $value): mixed
    {

        if (! isset($this->lookupFields[$field]) || ! $value) {
            return $value;
        }

        $model = $this->lookupFields[$field];
        $fvalue = \is_array($value) ? $value[$field] : $value;

        if ($field === 'list_of_assets') {
            if (\is_array($fvalue)) {
                return $model::whereIn('id', $fvalue)->pluck('short_description')->implode(', ');
            }
        }

        if ($model === User::class) {
            return $model::find($fvalue)?->name ?? $fvalue;
        }

        if ($model === tickets::class) {
            return $model::find($fvalue)?->number ?? $fvalue;
        }

        if($model === Carbon::class) {
            return Carbon::parse($fvalue)->format('d-m-Y H:i:s');
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

    public function flattenActivities(iterable $activities, int $perPage = 20)
    {
        $changes = collect();
        $updated = [];

        foreach ($activities as $activity) {

            $newValues = $activity->attribute_changes->get('attributes', []);
            $oldValues = $activity->attribute_changes->get('old', []);

            foreach ($newValues as $field => $newValue) {

                if (empty($oldValues)) {
                    continue;
                }

                if ($oldValues[$field] == $newValue) {
                    continue;
                }

                if ($field == 'updated_at') {
                    $updated[] = ['oldValues' => $oldValues[$field], 'newValue' => $newValue];
                }

                $changes->push([
                    'date' => $activity->created_at->format('d-m-Y H:i'),
                    'user' => $activity->causer?->name,
                    'event' => $activity->event,
                    'field' => $field,
                    'table' => $activity->log_name,
                    'old' => $this->formatValue($field, $oldValues[$field]),
                    'new' => $this->formatValue($field, $newValues),
                ]);
            }

        }

        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            items: $changes->forPage($page, $perPage)->values(),
            total: $changes->count(),
            perPage: $perPage,
            currentPage: $page,
            options: [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ],
        );

    }
}
