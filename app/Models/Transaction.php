<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use App\Enums\ProcessStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    /**
     * Kolom status (approval_status, process_status), admin_note, processed_by,
     * completed_at dan tanggal serah-terima SENGAJA tidak ada di sini.
     * Hanya TransactionWorkflowService (Tahap 5) yang boleh mengubahnya,
     * sehingga siswa tidak bisa memanipulasi status lewat request.
     */
    protected $fillable = [
        'transaction_code', 'user_id', 'service_type_id', 'document_id',
        'request_date', 'planned_date', 'planned_return_date', 'description',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'planned_date' => 'date',
            'planned_return_date' => 'date',
            'actual_handover_date' => 'date',
            'actual_return_date' => 'date',
            'completed_at' => 'datetime',
            'approval_status' => ApprovalStatus::class,
            'process_status' => ProcessStatus::class,
        ];
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function detail(): HasOne
    {
        return $this->hasOne(TransactionDetail::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(TransactionFile::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TransactionStatusHistory::class)->orderBy('changed_at');
    }
}
