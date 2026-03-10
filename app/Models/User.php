<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'nip',
        'role',
        'password',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        // Hanya role panel (super_admin, admin, observer) yang boleh akses /admin
        // Role 'user' (peserta) hanya boleh akses /ujian
        return in_array($this->role, ['super_admin', 'admin', 'observer'], true);
    }

    /**
     * Boot: sinkronkan kolom `role` DB dengan Spatie role setiap kali disimpan.
     * Peserta ujian (role = 'user') tidak perlu Spatie role.
     */
    protected static function booted(): void
    {
        static::saved(static function (User $user): void {
            // Hanya sync jika kolom role berubah
            if (! $user->wasChanged('role')) {
                return;
            }

            // Peserta ujian tidak butuh Spatie role
            if ($user->role === 'user') {
                $user->syncRoles([]);

                return;
            }

            // Sync Spatie role — hanya jika role tersebut ada di tabel roles
            if (\Spatie\Permission\Models\Role::where('name', $user->role)->where('guard_name', 'web')->exists()) {
                $user->syncRoles([$user->role]);
            }
        });
    }

    public function examPackages(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ExamPackage::class, 'exam_participants')
            ->using(ExamParticipant::class)
            // Sertakan kolom id agar pivot (ExamParticipant) juga punya primary key
            ->withPivot(['id', 'token', 'is_active'])
            ->withTimestamps();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
