<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Technology extends Model
{
    use HasFactory;

    protected $fillable = [
        'division_id',
        'name',
        'description',
        'order',
    ];

    /**
     * Get the division this technology belongs to
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Get media for this technology
     */
    public function media()
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('order');
    }

    /**
     * Scope for ordered technologies
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
