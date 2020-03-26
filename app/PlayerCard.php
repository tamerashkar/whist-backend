<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlayerCard extends Model
{
    protected $guarded = [
        'id'
    ];

    public $casts = [
        'value' => 'int'
    ];
}
