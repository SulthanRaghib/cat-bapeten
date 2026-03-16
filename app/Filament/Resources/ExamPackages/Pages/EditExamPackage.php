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
        return __('Edit: :name', ['name' => $this->record?->title ?? parent::getTitle()]);
    }

    // Show exam type as a subheading badge
    public function getSubheading(): ?string
    {
        $examType = $this->record->examType;

        if (!$examType) {
            return null;
        }

        $method = match ($examType->evaluation_method) {
            'weighted' => __('Weighted (Mansoskul)'),
            'correct_wrong' => __('Correct/Incorrect (Technical)'),
            default => $examType->evaluation_method,
        };

        return __('Exam Type: :name — Method: :method', ['name' => $examType->name, 'method' => $method]);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalHeading(__('Delete Exam Package?'))
                ->modalDescription(__('The exam package along with all its questions, NAB configuration, and related participant data will be permanently deleted. This action cannot be undone.'))
                ->modalSubmitActionLabel(__('Yes, Delete')),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label(__('Save Changes')),

            $this->getCancelFormAction()
                ->label(__('Cancel')),
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
        return __('Exam Details');
    }
}
