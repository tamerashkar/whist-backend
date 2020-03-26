<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class GamePlayer extends Pivot
{
    public static $pivots = [
        'bid',
        'team',
        'suit',
        'value',
        'position',
        'bid_winner',
        'hand_winner',
        'hand_wins',
    ];
}
