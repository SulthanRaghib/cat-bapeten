<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExamPackage;
use App\Models\QuestionUnit;
use App\Models\QuestionUnitIndicator;
use Illuminate\Support\Facades\DB;

/**
 * Service for syncing the `unit_scoring_configs` JSON column on ExamPackage
 * with the actual questions currently attached to the package.
 *
 * Bidirectional sync architecture:
 *  - Units are driven by questions (add/remove questions → add/remove units)
 *  - Indicator edits in the package config propagate BACK to Master Data
 *    (`question_unit_indicators` table) on save
 *
 * Called from:
 *  - NabConfigurationRelationManager (manual "Sync" button + save action)
 *  - QuestionsRelationManager (auto-sync after attach/detach/generate)
 */
final class NabSyncService
{
    /**
     * Perform a "Smart Sync" on the given ExamPackage:
     *
     *  1. Fetch DB Truth — unique question_unit_ids from currently attached questions.
     *  2. Get Current State — the existing unit_scoring_configs from the DB.
     *  3. Build New State:
     *     - If a unit_id already exists in Current State → KEEP (with fresh master indicators).
     *     - If a unit_id is new → build from Master Data template.
     *     - Any unit_id NOT in DB Truth is simply dropped (stale removal).
     *  4. Persist to DB.
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

        // ── Step 2: Current State — existing configs from DB ──
        $currentState = collect($package->unit_scoring_configs ?? [])
            ->values()
            ->keyBy(fn(array $item): int => (int) ($item['question_unit_id'] ?? 0))
            ->toArray();

        // ── Step 3: Build fresh templates for ALL units from master data ──
        $allUnits = QuestionUnit::with('indicators')
            ->whereIn('id', $dbUnitIds)
            ->get()
            ->keyBy('id');

        $newState = [];
        $kept = 0;
        $added = 0;

        foreach ($dbUnitIds as $unitId) {
            if (isset($currentState[$unitId])) {
                // ── KEEP — but refresh from master data (bidirectional = master is truth) ──
                $unit = $allUnits[$unitId] ?? null;
                if ($unit) {
                    $newState[] = self::buildTemplateForUnit($unit);
                } else {
                    // Fallback: keep existing
                    $existing = $currentState[$unitId];
                    $existing['indicators'] = array_values($existing['indicators'] ?? []);
                    $newState[] = $existing;
                }
                $kept++;
            } elseif (isset($allUnits[$unitId])) {
                // ── NEW unit — build from Master Data template ──
                $newState[] = self::buildTemplateForUnit($allUnits[$unitId]);
                $added++;
            } else {
                // Unit exists in questions but has no master data (edge case)
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
     * Sync indicator edits from the package JSON config back to Master Data.
     *
     * For each unit in the config:
     *  - Indicators WITH `indicator_id` → update the master record
     *  - Indicators WITHOUT `indicator_id` (new) → create in master, return new ID
     *  - Master indicators NOT present in the config → delete from master
     *
     * @return array{updated: int, created: int, deleted: int}
     */
    public function syncIndicatorsToMaster(ExamPackage $package, array $configs): array
    {
        $updated = 0;
        $created = 0;
        $deleted = 0;

        DB::transaction(function () use ($configs, &$updated, &$created, &$deleted): void {
            foreach ($configs as &$unitConfig) {
                $unitId = (int) ($unitConfig['question_unit_id'] ?? 0);
                if ($unitId === 0) {
                    continue;
                }

                // Get current master indicators for this unit
                $masterIndicatorIds = QuestionUnitIndicator::where('question_unit_id', $unitId)
                    ->pluck('id')
                    ->toArray();

                $configIndicatorIds = [];
                $sortOrder = 0;

                foreach (($unitConfig['indicators'] ?? []) as $index => &$indicator) {
                    $indicatorId = ! empty($indicator['indicator_id']) ? (int) $indicator['indicator_id'] : null;
                    $sortOrder++;

                    $data = [
                        'question_unit_id' => $unitId,
                        'name'             => $indicator['name'] ?? 'Indikator',
                        'min_score'        => (int) ($indicator['min_score'] ?? 0),
                        'max_score'        => (int) ($indicator['max_score'] ?? 0),
                        'is_passing'       => (bool) ($indicator['is_passing'] ?? false),
                        'sort_order'       => $sortOrder,
                    ];

                    if ($indicatorId && in_array($indicatorId, $masterIndicatorIds)) {
                        // ── UPDATE existing master record ──
                        QuestionUnitIndicator::where('id', $indicatorId)->update($data);
                        $configIndicatorIds[] = $indicatorId;
                        $updated++;
                    } else {
                        // ── CREATE new master record ──
                        $newIndicator = QuestionUnitIndicator::create($data);
                        $indicator['indicator_id'] = $newIndicator->id;
                        $configIndicatorIds[] = $newIndicator->id;
                        $created++;
                    }
                }

                // ── DELETE master indicators no longer in the config ──
                $toDelete = array_diff($masterIndicatorIds, $configIndicatorIds);
                if (! empty($toDelete)) {
                    QuestionUnitIndicator::whereIn('id', $toDelete)->delete();
                    $deleted += count($toDelete);
                }

                // Update the reference back
                $unitConfig['indicators'] = array_values($unitConfig['indicators'] ?? []);
            }

            // Note: $configs is passed by value, but we need to return the updated version
            // We'll handle this outside the transaction
        });

        return ['updated' => $updated, 'created' => $created, 'deleted' => $deleted];
    }

    /**
     * Sync indicators to master AND update the package JSON with fresh indicator_ids.
     * This is the main entry point used by the Save button.
     *
     * @return array{configs: array, updated: int, created: int, deleted: int}
     */
    public function saveWithMasterSync(ExamPackage $package, array $configs): array
    {
        $updated = 0;
        $created = 0;
        $deleted = 0;

        $configs = DB::transaction(function () use ($configs, &$updated, &$created, &$deleted): array {
            foreach ($configs as &$unitConfig) {
                $unitId = (int) ($unitConfig['question_unit_id'] ?? 0);
                if ($unitId === 0) {
                    continue;
                }

                // Get current master indicators for this unit
                $masterIndicatorIds = QuestionUnitIndicator::where('question_unit_id', $unitId)
                    ->pluck('id')
                    ->toArray();

                $configIndicatorIds = [];
                $sortOrder = 0;

                $indicators = array_values($unitConfig['indicators'] ?? []);
                foreach ($indicators as &$indicator) {
                    $indicatorId = ! empty($indicator['indicator_id']) ? (int) $indicator['indicator_id'] : null;
                    $sortOrder++;

                    $data = [
                        'question_unit_id' => $unitId,
                        'name'             => $indicator['name'] ?? 'Indikator',
                        'min_score'        => (int) ($indicator['min_score'] ?? 0),
                        'max_score'        => (int) ($indicator['max_score'] ?? 0),
                        'is_passing'       => (bool) ($indicator['is_passing'] ?? false),
                        'sort_order'       => $sortOrder,
                    ];

                    if ($indicatorId && in_array($indicatorId, $masterIndicatorIds)) {
                        // ── UPDATE existing master record ──
                        QuestionUnitIndicator::where('id', $indicatorId)->update($data);
                        $configIndicatorIds[] = $indicatorId;
                        $updated++;
                    } else {
                        // ── CREATE new master record ──
                        $newIndicator = QuestionUnitIndicator::create($data);
                        $indicator['indicator_id'] = $newIndicator->id;
                        $configIndicatorIds[] = $newIndicator->id;
                        $created++;
                    }
                }

                // ── DELETE master indicators no longer in the config ──
                $toDelete = array_diff($masterIndicatorIds, $configIndicatorIds);
                if (! empty($toDelete)) {
                    QuestionUnitIndicator::whereIn('id', $toDelete)->delete();
                    $deleted += count($toDelete);
                }

                $unitConfig['indicators'] = $indicators;
            }

            return $configs;
        });

        // Persist the updated JSON (with new indicator_ids) to the package
        $cleanConfigs = array_values($configs);
        $package->update(['unit_scoring_configs' => $cleanConfigs]);

        return [
            'configs' => $cleanConfigs,
            'updated' => $updated,
            'created' => $created,
            'deleted' => $deleted,
        ];
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
