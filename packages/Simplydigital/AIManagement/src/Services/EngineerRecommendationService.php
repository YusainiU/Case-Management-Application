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
            ->with([
                'user',
                'skills' => fn ($query) => $query->where('active', true)
                    ->whereNull('deleted_at')->with('skill'),
                'certifications' => fn ($query) => $query->where('active', true)
                    ->where('expired', false)
                    ->whereNull('deleted_at')
                    ->whereNotNull('expiry_date')
                    ->where('expiry_date', '>', now())
                    ->with('certificate'),
            ])
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

            // A certificate is evidence only if the active record has a future
            // expiry date and its exact name is mentioned in the case.
            $certifications = [];
            foreach ($engineer->certifications as $record) {
                $certificate = $record->certificate;
                if (! $certificate || ! $certificate->active || $certificate->deleted_at !== null) {
                    continue;
                }

                $name = trim((string) $certificate->name);
                if ($name === '') {
                    continue;
                }

                $pattern = '/(?<![\\p{L}\\p{N}])'.preg_quote($name, '/').'(?![\\p{L}\\p{N}])/iu';
                if (preg_match($pattern, $text)) {
                    $certifications[] = $name;
                }
            }

            $matched = array_values(array_unique($matched));
            $certifications = array_values(array_unique($certifications));
            $workload = (int) ($loads[$engineer->user_id] ?? 0);
            $results[] = [
                'engineer_id' => $engineer->id,
                'user_id' => (int) $engineer->user_id,
                'name' => $engineer->user?->name ?? 'Unknown user',
                'matched_skills' => $matched,
                'matched_certifications' => $certifications,
                'listed_skills' => array_values(array_unique($listed)),
                'open_cases' => $workload,
                'evidence' => count($matched) > 0
                    ? 'skill_match'
                    : (count($certifications) > 0 ? 'certification_match' : 'workload_only'),
            ];
        }

        usort($results, static fn (array $a, array $b): int =>
            (count($b['matched_skills']) <=> count($a['matched_skills']))
            ?: (count($b['matched_certifications']) <=> count($a['matched_certifications']))
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
