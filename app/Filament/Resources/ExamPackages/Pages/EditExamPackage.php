<?php

namespace App\Filament\Resources\ExamPackages\Pages;

use App\Filament\Resources\ExamPackages\ExamPackageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\Url;

class EditExamPackage extends EditRecord
{
    protected static string $resource = ExamPackageResource::class;

    // Menyimpan parameter 'return_url' dari query string secara otomatis.
    // Ini bertindak sebagai pengganti hidden input untuk state management di Livewire.
    #[Url]
    public ?string $return_url = null;

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
            DeleteAction::make()
                ->modalHeading('Hapus Paket Ujian?')
                ->modalDescription('Paket ujian beserta seluruh soal, konfigurasi NAB, dan data peserta yang terkait akan dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.')
                ->modalSubmitActionLabel('Ya, Hapus'),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Simpan Perubahan'),

            $this->getCancelFormAction()
                ->label('Batal'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        // Jika ada parameter return_url, gunakan itu (misal dari Dashboard)
        if ($this->return_url) {
            return $this->return_url;
        }

        // Default: Kembali ke halaman index Resource (Menu Edit Ujian)
        return $this->getResource()::getUrl('index');
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
