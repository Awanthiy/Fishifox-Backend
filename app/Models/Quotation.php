<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_number',
        'customer_id',
        'customer',
        'customer_email',
        'customer_phone',
        'customer_address',
        'amount',
        'currency',
        'quote_date',
        'status',
        'converted',
        'converted_at',
    ];

    protected $casts = [
        'quote_date' => 'date',
        'converted' => 'boolean',
        'converted_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function services()
    {
        return $this->hasMany(QuotationService::class);
    }
}