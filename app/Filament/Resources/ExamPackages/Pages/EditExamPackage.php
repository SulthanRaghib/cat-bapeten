<?php

namespace App\Filament\Resources\ExamPackages\Pages;

use App\Filament\Resources\ExamPackages\ExamPackageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExamPackage extends EditRecord
{
    protected static string $resource = ExamPackageResource::class;

    // Dynamic title showing the package name
    public function getTitle(): string
    {
        return 'Edit: ' . ($this->record->name ?? 'Paket Ujian');
    }

    // Show exam type as a subheading badge
    public function getSubheading(): ?string
    {
        $examType = $this->record->examType;

        if (! $examType) {
            return null;
        }

        $method = match ($examType->evaluation_method) {
            'weighted' => 'Pembobotan (Mansoskul)',
            'correct_wrong' => 'Benar/Salah (Teknis)',
            default => $examType->evaluation_method,
        };

        return "Tipe Ujian: {$examType->name} — Metode: {$method}";
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        // Stay on the edit page after saving — needed so relation manager tabs remain accessible.
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }

    // UX Improvement: Menggabungkan Form dan Relation Manager dalam Tab Sejajar
    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    // Mengubah label Tab Form utama (defaultnya "Edit")
    public function getContentTabLabel(): ?string
    {
        return 'Detail Ujian';
    }
}
