<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDetail extends Model
{
    protected $fillable = [
        'transaction_id', 'legalization_doc_type', 'copies',
        'pickup_time_start', 'pickup_time_end', 'contact_phone',
        'receiver_name', 'receiver_relation', 'receiver_id_number',
        'handover_note', 'return_condition_note', 'handover_proof_file_id',
    ];

    protected function casts(): array
    {
        return ['copies' => 'integer'];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function handoverProof(): BelongsTo
    {
        return $this->belongsTo(TransactionFile::class, 'handover_proof_file_id');
    }
}
