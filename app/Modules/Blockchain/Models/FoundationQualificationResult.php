<?php

namespace App\Modules\Blockchain\Models;

use Illuminate\Database\Eloquent\Model;

class FoundationQualificationResult extends Model
{
    protected $table = 'foundation_qualification_results';

    protected $fillable = [
        'user_id',
        'threshold',
        'cond1_value', 'cond1_met',
        'cond2_value', 'cond2_met',
        'cond3_value', 'cond3_met',
        'cond4_value', 'cond4_met',
        'cond5_value', 'cond5_met',
        'is_qualified',
        'checked_at',
    ];

    protected $casts = [
        'cond1_met' => 'boolean',
        'cond2_met' => 'boolean',
        'cond3_met' => 'boolean',
        'cond4_met' => 'boolean',
        'cond5_met' => 'boolean',
        'is_qualified' => 'boolean',
        'checked_at' => 'datetime',
    ];
}
