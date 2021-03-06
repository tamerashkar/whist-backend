<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GameEvent extends Model
{
    protected $guarded = [
        'id'
    ];

    public $casts = [
        'data' => 'json'
    ];
}
