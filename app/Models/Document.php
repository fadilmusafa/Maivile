<?php

namespace App\Models;

use App\Enums\DocumentAvailability;
use App\Enums\DocumentKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'owner_user_id', 'name', 'document_number', 'academic_year',
        'description', 'document_kind', 'availability', 'physical_location',
        'file_path', 'file_mime',
    ];

    protected function casts(): array
    {
        return [
            'document_kind' => DocumentKind::class,
            'availability' => DocumentAvailability::class,
        ];
    }

    public function isDigital(): bool
    {
        return $this->document_kind === DocumentKind::Digital;
    }

    /** File digital baru dianggap ada bila kind = digital DAN file_path terisi. */
    public function hasDigitalFile(): bool
    {
        return $this->isDigital() && filled($this->file_path);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
