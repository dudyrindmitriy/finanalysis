<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'type',
        'date',
        'time',
        'description',
        'bank_name',
        'bank_category',
        'mcc_code',
        'category_id',
        'user_id',
        'transaction_hash'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
