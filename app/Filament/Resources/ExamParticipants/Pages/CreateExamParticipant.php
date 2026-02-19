<?php

namespace App\Filament\Resources\ExamParticipants\Pages;

use App\Filament\Resources\ExamParticipants\ExamParticipantResource;
use App\Models\ExamParticipant;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateExamParticipant extends CreateRecord
{
    protected static string $resource = ExamParticipantResource::class;

    protected static ?string $title = 'Tambah Peserta Ujian';

    public function getBreadcrumb(): string
    {
        return 'Tambah';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Simpan'),

            $this->getCreateAnotherFormAction()
                ->label('Simpan & Tambah Lagi'),

            $this->getCancelFormAction()
                ->label('Batal'),
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {
        $users = $data['user_id'];

        // Ensure users is an array (handle single selection if multiple was false for some reason)
        if (!is_array($users)) {
            $users = [$users];
        }

        $examPackageId = $data['exam_package_id'];
        $isActive = $data['is_active'] ?? true;

        $createdRecord = null;

        foreach ($users as $userId) {
            $token = strtoupper(Str::random(6));

            // Ensure token uniqueness
            while (ExamParticipant::where('exam_package_id', $examPackageId)->where('token', $token)->exists()) {
                $token = strtoupper(Str::random(6));
            }

            $record = ExamParticipant::create([
                'user_id' => $userId,
                'exam_package_id' => $examPackageId,
                'token' => $token,
                'is_active' => $isActive,
            ]);

            if (! $createdRecord) {
                $createdRecord = $record;
            }
        }

        return $createdRecord;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
