<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionFile extends Model
{
    public const TYPE_REQUIREMENT = 'requirement';
    public const TYPE_POWER_OF_ATTORNEY = 'power_of_attorney';
    public const TYPE_HANDOVER_PROOF = 'handover_proof';

    protected $fillable = [
        'transaction_id', 'uploaded_by', 'type', 'original_name',
        'file_path', 'mime_type', 'file_size',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
