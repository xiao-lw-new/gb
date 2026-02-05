<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressSignCode extends Model
{
    protected $table = 'address_sign_code';

    protected $fillable = [
        'address',
        'type',
        'code',
        'expired',
        'retry',
        'expired_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
    ];
}

