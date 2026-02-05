<?php

namespace App\Modules\Member\Models;

use Illuminate\Database\Eloquent\Model;

class UserRelation extends Model
{
    protected $table = 'user_relations';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ancestor_id',
        'distance',
    ];

    public function ancestor()
    {
        return $this->belongsTo(\App\Models\User::class, 'ancestor_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
