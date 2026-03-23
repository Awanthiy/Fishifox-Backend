<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'customer_type',
        'contact_person',
        'address',
        'status',
    ];

    protected $casts = [
        'active_projects' => 'integer',
        'total_billed' => 'decimal:2',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}