<?php

namespace Simplydigital\AIManagement\Services;

use Illuminate\Support\Str;
use Simplydigital\CaseManagement\Models\tickets;
use Simplydigital\EngineerManagement\Models\EngineerManagement;

class EngineerRecommendationService
{
    /**
     * Read-only, evidence-based ranking. Workload is an open-case COUNT, not a
     * claim about calendar availability, working hours, or capacity.
     */
    public function recommend(tickets $case): array
    {
        $text = Str::lower(trim(($case->short_description ?? '').' '.($case->description ?? '')));
        $engineers = EngineerManagement::query()
            ->where('active', true)
            ->whereNull('deleted_at')
            ->whereHas('user')
            ->with(['user', 'skills' => fn ($query) => $query->where('active', true)
                ->whereNull('deleted_at')->with('skill')])
            ->get();

        $userIds = $engineers->pluck('user_id')->unique()->all();
        $loads = tickets::query()
            ->whereIn('assigned_to', $userIds)
            ->whereNull('closed_datetime')
            ->whereNull('deleted_at')
            ->selectRaw('assigned_to, COUNT(*) as total')
            ->groupBy('assigned_to')
            ->pluck('total', 'assigned_to');

        $results = [];
        foreach ($engineers as $engineer) {
            $matched = [];
            $listed = [];
            foreach ($engineer->skills as $link) {
                $skill = $link->skill;
                if (! $skill || ! $skill->active || $skill->deleted_at !== null) {
                    continue;
                }
                $name = trim((string) ($skill->skills ?? ''));
                if ($name === '') {
                    continue;
                }
                $listed[] = $name;
                // Whole-phrase match avoids arbitrary substring matches.
                $pattern = '/(?<![\p{L}\p{N}])'.preg_quote($name, '/').'(?![\p{L}\p{N}])/iu';
                if (preg_match($pattern, $text)) {
                    $matched[] = $name;
                }
            }

            $workload = (int) ($loads[$engineer->user_id] ?? 0);
            $results[] = [
                'engineer_id' => $engineer->id,
                'user_id' => (int) $engineer->user_id,
                'name' => $engineer->user?->name ?? 'Unknown user',
                'matched_skills' => array_values(array_unique($matched)),
                'listed_skills' => array_values(array_unique($listed)),
                'open_cases' => $workload,
                'evidence' => count($matched) > 0 ? 'skill_match' : 'workload_only',
            ];
        }

        usort($results, static fn (array $a, array $b): int =>
            (count($b['matched_skills']) <=> count($a['matched_skills']))
            ?: ($a['open_cases'] <=> $b['open_cases'])
            ?: ($a['user_id'] <=> $b['user_id'])
        );

        return $results;
    }

    public function eligibleUser(int $userId): bool
    {
        return EngineerManagement::query()
            ->where('user_id', $userId)
            ->where('active', true)
            ->whereNull('deleted_at')
            ->whereHas('user')
            ->exists();
    }
}
