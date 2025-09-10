<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_name',
        'filename',
        'path',
        'mime_type',
        'size',
        'gcs_path',
        'is_published',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'size' => 'integer',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPublicUrlAttribute()
    {
        if (! $this->is_published) {
            return null;
        }

        return route('public.files.show', ['id' => $this->id]);
    }

    public function getInternalUrlAttribute()
    {
        return route('internal.files.show', ['id' => $this->id]);
    }
}
