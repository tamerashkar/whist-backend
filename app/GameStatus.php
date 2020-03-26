<?php

namespace App;

class GameStatus extends Enum
{
    const DEFAULT_VALUE = 0;

    const TEAM_SELECTION = 0;
    const START = 1;
    const ROUND = 3;
    const BIDDING = 5;
    const COMPETE = 10;
    const WINNER = 100;

    const VALUES = [
        0 => 'team_selection',
        1 => 'start',
        3 => 'round',
        5 => 'bidding',
        10 => 'compete',
        100 => 'winner'
    ];
}
