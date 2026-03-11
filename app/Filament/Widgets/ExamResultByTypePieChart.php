<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\ExamPackage;
use App\Models\ExamSession;
use App\Models\ExamType;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class ExamResultByTypePieChart extends Widget
{
    protected string $view = 'filament.widgets.exam-result-by-type-pie-chart';
    protected static ?int $sort = 10;
    protected int|string|array $columnSpan = 'full';

    /** Filter periode yang dipilih admin */
    public string $period = 'today';

    /** Tanggal custom (opsional) */
    public string $customFrom = '';
    public string $customTo   = '';

    public function resetCustomFilter(): void
    {
        $this->customFrom = '';
        $this->customTo   = '';
        $this->period     = 'today';
    }

    /**
     * Ambil ID paket ujian berdasarkan periode yang dipilih.
     */
    private function getPackageIds(): \Illuminate\Support\Collection
    {
        $query = ExamPackage::query();

        match ($this->period) {
            'today'  => $query->whereDate('start_time', Carbon::today()),
            'week'   => $query->whereBetween('start_time', [
                Carbon::now()->startOfWeek()->startOfDay(),
                Carbon::now()->endOfWeek()->endOfDay(),
            ]),
            'month'  => $query->whereBetween('start_time', [
                Carbon::now()->startOfMonth()->startOfDay(),
                Carbon::now()->endOfMonth()->endOfDay(),
            ]),
            'custom' => $query->when(
                $this->customFrom,
                fn($q) => $q->where('start_time', '>=', Carbon::parse($this->customFrom)->startOfDay())
            )->when(
                $this->customTo,
                fn($q) => $q->where('start_time', '<=', Carbon::parse($this->customTo)->endOfDay())
            ),
            default  => null, // 'all' — tidak ada filter tanggal
        };

        return $query->pluck('id');
    }

    public function getViewData(): array
    {
        $examTypes  = ExamType::where('is_active', true)->orderBy('name')->get();
        $packageIds = $this->getPackageIds();

        if ($packageIds->isEmpty()) {
            return ['chartData' => []];
        }

        $sessions = ExamSession::with([
            'examPackage' => fn($q) => $q->select(
                'exam_packages.id',
                'exam_packages.passing_grade',
                'exam_packages.exam_type_id'
            ),
        ])
            ->where('status', 'completed')
            ->whereHas('examParticipant', fn($q) => $q->whereIn('exam_package_id', $packageIds))
            ->get(['id', 'exam_participant_id', 'status', 'total_score']);

        $chartData = [];

        foreach ($examTypes as $type) {
            $typeSessions = $sessions->filter(
                fn($s) => $s->examPackage?->exam_type_id === $type->id
            );

            if ($typeSessions->isEmpty()) {
                continue;
            }

            $lulus      = $typeSessions->filter(fn($s) => ($s->total_score ?? 0) >= ($s->examPackage?->passing_grade ?? 0))->count();
            $tidakLulus = $typeSessions->filter(fn($s) => ($s->total_score ?? 0) <  ($s->examPackage?->passing_grade ?? 0))->count();

            $chartData[] = [
                'name'       => $type->name,
                'lulus'      => $lulus,
                'tidakLulus' => $tidakLulus,
                'total'      => $typeSessions->count(),
            ];
        }

        return ['chartData' => $chartData];
    }
}
