<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo_path',
        'url',
        'order',
    ];

    /**
     * Get media for this client
     */
    public function media()
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('order');
    }

    /**
     * Scope for homepage display (max 12 clients)
     */
    public function scopeForHomepage($query)
    {
        return $query->orderBy('order')->limit(12);
    }
}
