<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExamPackage;
use App\Models\QuestionUnit;
use App\Models\QuestionUnitIndicator;

/**
 * Service for managing the `unit_scoring_configs` JSON column on ExamPackage.
 *
 * Per-package independent scoring architecture:
 *  - Units are DRIVEN by questions (add/remove questions → add/remove units).
 *  - When a unit is FIRST added, master data indicators are used as a TEMPLATE
 *    (starting point only — names & range values are copied, not linked).
 *  - After seeding, each package owns its indicator values independently.
 *    Saving a package config NEVER writes back to the master data table.
 *  - "Reset to master" is available per-unit to restore the original template.
 *
 * Called from:
 *  - NabConfigurationRelationManager (Sync button + Save button + Reset action)
 *  - QuestionsRelationManager (auto-sync after attach/detach/generate)
 */
final class NabSyncService
{
    /**
     * Perform a "Smart Sync" on the given ExamPackage:
     *
     *  1. Fetch DB Truth — unique question_unit_ids from currently attached questions.
     *  2. Get Current State — the existing per-package unit_scoring_configs from the DB.
     *  3. Build New State:
     *     - If a unit_id already EXISTS in Current State → KEEP as-is (preserve per-package values).
     *       Only the unit_name is refreshed from master (vocabulary sync).
     *     - If a unit_id is NEW → seed indicator template from Master Data (starting point only).
     *     - Any unit_id NOT in DB Truth is dropped (stale removal).
     *  4. Persist to DB.
     *
     * Note: existing per-package indicator customisations are NEVER overwritten.
     *
     * @return array{configs: array, added: int, removed: int, kept: int}
     */
    public function smartSync(ExamPackage $package): array
    {
        // Always work with fresh data
        $package = $package->fresh();

        // Skip if the package doesn't use weighted evaluation
        if ($package->examType?->evaluation_method !== 'weighted') {
            return ['configs' => [], 'added' => 0, 'removed' => 0, 'kept' => 0];
        }

        // ── Step 1: DB Truth — current unique unit IDs from attached questions ──
        $dbUnitIds = $package->questions()
            ->whereNotNull('questions.question_unit_id')
            ->pluck('questions.question_unit_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // If no questions with units, clear the config
        if (empty($dbUnitIds)) {
            $package->update(['unit_scoring_configs' => []]);

            return ['configs' => [], 'added' => 0, 'removed' => 0, 'kept' => 0];
        }

        // ── Step 2: Current State — existing per-package configs ──
        $currentState = collect($package->unit_scoring_configs ?? [])
            ->values()
            ->keyBy(fn(array $item): int => (int) ($item['question_unit_id'] ?? 0))
            ->toArray();

        // ── Step 3a: Load master data only for NEW units (starting-point template) ──
        $newUnitIds = array_values(array_filter(
            $dbUnitIds,
            fn(int $id): bool => !isset($currentState[$id])
        ));

        $masterUnitModels = collect();
        if (!empty($newUnitIds)) {
            $masterUnitModels = QuestionUnit::with('indicators')
                ->whereIn('id', $newUnitIds)
                ->get()
                ->keyBy('id');
        }

        // ── Step 3b: Refresh unit_name for KEPT units (vocabulary sync only) ──
        $existingUnitIds = array_values(array_filter(
            $dbUnitIds,
            fn(int $id): bool => isset($currentState[$id])
        ));
        $masterNames = [];
        if (!empty($existingUnitIds)) {
            $masterNames = QuestionUnit::whereIn('id', $existingUnitIds)
                ->pluck('name', 'id')
                ->toArray();
        }

        $newState = [];
        $kept     = 0;
        $added    = 0;

        foreach ($dbUnitIds as $unitId) {
            if (isset($currentState[$unitId])) {
                // ── KEEP — preserve all per-package indicator values ──
                // Only unit_name is refreshed from master (vocabulary sync).
                $existing               = $currentState[$unitId];
                $existing['unit_name']  = $masterNames[$unitId] ?? $existing['unit_name'];
                $existing['indicators'] = array_values($existing['indicators'] ?? []);
                $newState[] = $existing;
                $kept++;
            } elseif ($masterUnitModels->has($unitId)) {
                // ── NEW unit — seed indicator template from master (starting point only) ──
                $template = self::buildTemplateForUnit($masterUnitModels[$unitId]);
                // Strip indicator_id — per-package config does not reference master IDs
                $template['indicators'] = array_map(
                    fn(array $ind): array => array_diff_key($ind, ['indicator_id' => true]),
                    $template['indicators']
                );
                $newState[] = $template;
                $added++;
            } else {
                // Edge case: unit in questions but no master data record
                $newState[] = [
                    'question_unit_id' => $unitId,
                    'unit_name'        => "Unit #{$unitId}",
                    'indicators'       => [],
                ];
                $added++;
            }
        }

        $removed = count($currentState) - $kept;

        // ── Step 4: Persist to DB ──
        $cleanState = array_values($newState);
        $package->update(['unit_scoring_configs' => $cleanState]);

        return [
            'configs' => $cleanState,
            'added'   => $added,
            'removed' => max(0, $removed),
            'kept'    => $kept,
        ];
    }

    /**
     * Save the per-package indicator config to the JSON column ONLY.
     *
     * Does NOT touch the master data table (`question_unit_indicators`).
     * Each package owns its scoring rules independently.
     *
     * @return array{configs: array}
     */
    public function savePackageConfig(ExamPackage $package, array $configs): array
    {
        $cleanConfigs = collect($configs)
            ->values()
            ->map(function (array $unit): array {
                // Strip indicator_id — no longer referenced in per-package storage
                $unit['indicators'] = array_map(
                    fn(array $ind): array => array_diff_key($ind, ['indicator_id' => true]),
                    array_values($unit['indicators'] ?? [])
                );

                return $unit;
            })
            ->toArray();

        $package->update(['unit_scoring_configs' => $cleanConfigs]);

        return ['configs' => $cleanConfigs];
    }

    /**
     * Reset a single unit's indicators back to the current master data template.
     *
     * Useful when the admin wants to revert per-package customisations
     * for one specific unit without running a full sync.
     *
     * @return array Updated full unit_scoring_configs after the reset.
     */
    public function resetUnitToMaster(ExamPackage $package, int $unitId): array
    {
        $unit = QuestionUnit::with('indicators')->find($unitId);

        if (!$unit) {
            return $package->unit_scoring_configs ?? [];
        }

        $template = self::buildTemplateForUnit($unit);
        // Strip indicator_ids from per-package storage
        $template['indicators'] = array_map(
            fn(array $ind): array => array_diff_key($ind, ['indicator_id' => true]),
            $template['indicators']
        );

        $configs = collect($package->unit_scoring_configs ?? [])
            ->map(function (array $existing) use ($unitId, $template): array {
                return ((int) ($existing['question_unit_id'] ?? 0)) === $unitId
                    ? $template
                    : $existing;
            })
            ->values()
            ->toArray();

        $package->update(['unit_scoring_configs' => $configs]);

        return $configs;
    }

    /**
     * Build a full config from scratch (used for initial auto-sync when empty).
     *
     * @return array<int, array>
     */
    public function buildFullConfigs(ExamPackage $package): array
    {
        $package = $package->fresh();

        $unitIds = $package->questions()
            ->whereNotNull('questions.question_unit_id')
            ->pluck('questions.question_unit_id')
            ->unique()
            ->values()
            ->toArray();

        if (empty($unitIds)) {
            return [];
        }

        $units = QuestionUnit::with('indicators')
            ->whereIn('id', $unitIds)
            ->orderBy('name')
            ->get();

        return $units->map(fn(QuestionUnit $unit): array => self::buildTemplateForUnit($unit))
            ->values()
            ->toArray();
    }

    /**
     * Build a single unit config template from a QuestionUnit model.
     * Includes `indicator_id` for bidirectional sync tracking.
     */
    public static function buildTemplateForUnit(QuestionUnit $unit): array
    {
        // Ensure indicators are loaded
        if (! $unit->relationLoaded('indicators')) {
            $unit->load('indicators');
        }

        return [
            'question_unit_id' => $unit->id,
            'unit_name'        => $unit->name,
            'indicators'       => $unit->indicators
                ->map(fn(QuestionUnitIndicator $ind): array => [
                    'indicator_id' => $ind->id,
                    'name'         => $ind->name,
                    'min_score'    => $ind->min_score,
                    'max_score'    => $ind->max_score,
                    'is_passing'   => $ind->is_passing,
                ])
                ->values()
                ->toArray(),
        ];
    }
}
