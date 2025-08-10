<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'hero_image_path',
        'order',
    ];

    /**
     * Get products for this division
     */
    public function products()
    {
        return $this->hasMany(Product::class)->orderBy('order');
    }

    /**
     * Get technologies for this division
     */
    public function technologies()
    {
        return $this->hasMany(Technology::class)->orderBy('order');
    }

    /**
     * Get machines for this division
     */
    public function machines()
    {
        return $this->hasMany(Machine::class)->orderBy('order');
    }

    /**
     * Get media for this division
     */
    public function media()
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('order');
    }

    /**
     * Scope for ordered divisions
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Get route key name for model binding
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
}
