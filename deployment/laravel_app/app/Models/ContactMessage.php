<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company',
        'phone',
        'email',
        'subject',
        'message',
        'handled',
        'note',
        'created_by_ip',
        'user_agent',
    ];

    protected $casts = [
        'handled' => 'boolean',
    ];

    /**
     * Scope for unhandled messages
     */
    public function scopeUnhandled($query)
    {
        return $query->where('handled', false);
    }

    /**
     * Scope for searchable fields
     */
    public function scopeSearchable($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('company', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('created_by_ip', 'like', "%{$term}%");
        });
    }
}
