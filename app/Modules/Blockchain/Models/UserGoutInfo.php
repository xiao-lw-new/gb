<?php

namespace App\Modules\Blockchain\Models;

use Illuminate\Database\Eloquent\Model;

class UserGoutInfo extends Model
{
    protected $table = 'user_gout_info';
    protected $fillable = [
        'user_id',
        'burn_amount_new',
        'token_balance',
        'total_burn_amount',
        'new_gout_amount',
        'is_qualified',
        'invalid_uploaded',
    ];

    protected $casts = [
        'is_qualified' => 'boolean',
    ];
}
