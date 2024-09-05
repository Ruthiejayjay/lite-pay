<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_account_id',
        'receiver_account_id',
        'receiver_account_number',
        'receiver_account_holder_name',
        'currency_id',
        'amount',
        'status',
    ];
}
