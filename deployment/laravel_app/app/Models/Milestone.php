<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'text',
        'order',
    ];

    protected $casts = [
        'year' => 'integer',
    ];

    /**
     * Scope for ordering by year then order
     */
    public function scopeByYear($query)
    {
        return $query->orderBy('year')->orderBy('order');
    }
}
