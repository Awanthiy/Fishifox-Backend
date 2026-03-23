<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}