<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'customer_name',
        'customer_email',
        'amount',
        'currency',
        'billing_date',
        'status',
        'is_recurring',
        'recurrence_period',
        'next_run_date',
        'email_sent_at',
    ];

    protected $casts = [
        'billing_date' => 'date',
        'next_run_date' => 'date',
        'is_recurring' => 'boolean',
        'email_sent_at' => 'datetime',
        'amount' => 'decimal:2',
    ];
}