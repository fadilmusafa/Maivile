<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_STUDENT = 'student';

    public const REG_PENDING = 'pending';
    public const REG_APPROVED = 'approved';
    public const REG_REJECTED = 'rejected';

    /**
     * Sengaja TIDAK memuat role, is_active, must_change_password
     * agar tidak bisa diubah lewat mass assignment dari request.
     * Field tersebut diatur eksplisit oleh controller Admin.
     */
    protected $fillable = [
        'name', 'email', 'password', 'nis', 'nip', 'class_name', 'major', 'phone',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
            'registration_reviewed_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    public function roleLabel(): string
    {
        return $this->isAdmin() ? 'Admin / Petugas' : 'Siswa';
    }

    /** NIS untuk siswa, NIP untuk admin. */
    public function identifier(): ?string
    {
        return $this->isStudent() ? $this->nis : $this->nip;
    }

    /** Siswa yang sudah resmi (pendaftar yang belum/tidak disetujui tidak ikut dihitung). */
    public function scopeStudents(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_STUDENT)
            ->where('registration_status', self::REG_APPROVED);
    }

    /** Pendaftar mandiri yang menunggu atau ditolak. */
    public function scopeRegistrants(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_STUDENT)
            ->whereIn('registration_status', [self::REG_PENDING, self::REG_REJECTED]);
    }

    public function isRegistrationPending(): bool
    {
        return $this->registration_status === self::REG_PENDING;
    }

    public function isRegistrationRejected(): bool
    {
        return $this->registration_status === self::REG_REJECTED;
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'owner_user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }
}
