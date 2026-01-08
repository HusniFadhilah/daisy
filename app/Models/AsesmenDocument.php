<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsesmenDocument extends Model
{
    protected $table = 'asesmen_documents';

    protected $fillable = [
        'id_asesmen',
        'type',
        'title',
        'sort_order',
        'path',
        'original_name',
        'size',
        'mime',
        'is_active',
        'version',
        'uploaded_by',
        'uploaded_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'uploaded_at' => 'datetime',
    ];

    public function asesmen()
    {
        return $this->belongsTo(Asesmen::class, 'id_asesmen');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
