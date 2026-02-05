<?php

namespace App\Modules\Member\Models;

use Illuminate\Database\Eloquent\Model;

class UserTeamInfo extends Model
{
    protected $table = 'user_team_info';
    protected $fillable = ['user_id', 'subordinate_ids', 'large_area_id', 'small_area_ids', 'direct_referral_ids'];
    protected $casts = [
        'subordinate_ids' => 'array',
        'small_area_ids' => 'array',
        'direct_referral_ids' => 'array',
    ];
}

