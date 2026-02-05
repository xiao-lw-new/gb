<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Modules\Mg\Models\MgVipLevel;
use App\Modules\Mg\Models\MgSpecifiedVip;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public function vipLevel(): HasOne
    {
        return $this->hasOne(MgVipLevel::class, 'user_id');
    }

    public function specifiedVip(): HasOne
    {
        return $this->hasOne(MgSpecifiedVip::class, 'user_id');
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'address',
        'p_id',
        'path',
        'remark',
        'status',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

