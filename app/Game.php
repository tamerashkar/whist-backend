<?php

namespace App;

use Illuminate\Support\Facades\DB;
use App\Http\Resources\GameResource;
use App\Exceptions\DealerHasNotBeenSet;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    const HOME_TEAM = 1;
    const GUEST_TEAM = 2;
    const HOME_TEAM_NAME = 'Home';
    const GUEST_TEAM_NAME = 'Guest';
    const MAX_PLAYERS_PER_TEAM = 2;

    protected static $defaultPointsToWin = 52;

    protected $guarded = ['id'];

    public $casts = [
        'id' => 'string',
        'status' => 'int',
        'next_turn' => 'int',
        'home_team_points' => 'int',
        'guest_team_points' => 'int'
    ];

    public static function setup($players = [])
    {
        $game = Game::create([
            'next_turn' => 1,
            'start_turn' => 1,
            'status' => GameStatus::TEAM_SELECTION,
            'points_to_win' => static::defaultPointsToWin(),
        ]);

        foreach ($players as $key => $player) {
            $player->join($game, $key % 2 + 1);
        }

        return $game;
    }

    public function events()
    {
        return $this->hasMany(GameEvent::class);
    }

    public function unorderedPlayers()
    {
        return $this->belongsToMany(Player::class)->withPivot(GamePlayer::$pivots);
    }

    public function players()
    {
        return $this->unorderedPlayers()->orderBy('position');
    }

    public function activePlayer()
    {
        return $this->players()->where('position', $this->next_turn)->first();
    }

    public function player(Player $player)
    {
        return $this->players()->where('player_id', $player->id);
    }

    public function playersForTeam($team)
    {
        return $this->players()->wherePivot('team', $team);
    }

    public function playersOrderedFromLeftOfDealer()
    {
        $dealer = $this->dealer();

        $index = $this->players->search(function ($player) use ($dealer) {
            return $player->id === $dealer->id;
        });

        return $this->players
            ->slice($index + 1)
            ->concat($this->players->slice(0, $index))
            ->concat([$this->players->get($index)]);
    }

    public function gamePlayers()
    {
        return $this->hasMany(GamePlayer::class);
    }

    public function rounds()
    {
        return $this->hasMany(GameRound::class);
    }

    public function round()
    {
        return $this->hasOne(GameRound::class)->orderByDesc('id')->first();
    }

    public function roundFor(Player $player)
    {
        return $this->rounds()->latest()->where('player_id', $player->id)->first();
    }

    public function cards()
    {
        return $this->hasMany(PlayerCard::class);
    }

    public function status()
    {
        return GameStatus::find($this->status);
    }

    public static function defaultPointsToWin($points = null)
    {
        if ($points === null) return static::$defaultPointsToWin;
        static::$defaultPointsToWin = $points;
    }

    public function pointsToWin($points = null)
    {
        if ($points === null) return $this->points_to_win;
        $this->points_to_win = $points;
        return $this;
    }

    public function start($dealer = null)
    {
        if ($dealer) {
            $this->setDealer($dealer);
        }

        $this->update(['status' => GameStatus::START]);

        return $this;
    }

    public function continue()
    {
        if ($this->event) {
            $class = '\App\Events\\' . $this->event;

            // @todo(tamer) there cases where classes take multiple arguments.
            event(new $class($this));
        }
    }

    public function startBidding()
    {
        $this->update(['status' => GameStatus::BIDDING]);
        return $this;
    }

    public function startRound()
    {
        $this->update([
            'status' => GameStatus::ROUND,
            'trump_suit' => null
        ]);

        $this->gamePlayers()->update([
            'bid' => null,
            'suit' => null,
            'value' => null,
            'bid_winner' => false,
            'hand_winner' => false,
            'hand_wins' => false,
        ]);

        return $this;
    }

    public function setDealer(Player $player)
    {
        $this->dealer_position = $this->players()->where('player_id', $player->id)->first()->pivot->position;
        $this->save();

        return $this;
    }

    public function hasDealer()
    {
        return !!$this->dealer_position;
    }

    public function dealer()
    {
        if (!$this->dealer_position) {
            throw new DealerHasNotBeenSet();
        }

        return $this->players()->where('position', $this->dealer_position)->first();
    }

    public function deal(Deck $deck)
    {
        if (!$this->players()->count()) {
            // throw new MissingPlayersException();
            return false;
        }

        $now = now();
        $cards = [];
        $total = 13 * $this->players->count();
        $players = $this->playersOrderedFromLeftOfDealer();

        // Delete any lingering cards. This occurs when a round ends
        // with no winner such as when all players pass on the bid.
        foreach ($players as $player) {
            $player->cards()->delete();
        }

        while ($deck->count() > 52 - $total) {
            foreach ($players as $player) {
                $card = $deck->deal();

                $cards[] = [
                    'created_at' => $now,
                    'updated_at' => $now,
                    'game_id' => $this->id,
                    'player_id' => $player->id,
                    'suit' =>  $card->suit(),
                    'value' => $card->value(),
                ];
            }
        }

        PlayerCard::insert($cards);

        return $this;
    }

    public function setStartTurn($turn)
    {
        $this->update(['start_turn' => $turn]);
        return $this;
    }

    public function setNextTurn($turn)
    {
        $this->update(['next_turn' => $turn]);
        return $this;
    }

    public function nextTurn()
    {
        $this->update(['next_turn' => $this->next_turn % $this->players()->count() + 1]);
        return $this;
    }

    public function startTurnFrom($turn)
    {
        $this->update(['start_turn' => $turn % $this->players()->count() + 1]);
        return $this;
    }

    public function nextTurnFrom($turn)
    {
        $this->update(['next_turn' => $turn % $this->players()->count() + 1]);
        return $this;
    }

    public function receivedBidFrom(Player $player, $bid)
    {
        $this->players()->updateExistingPivot($player, ['bid' => $bid]);
        return $this;
    }

    public function hasReceivedAllBids()
    {
        return !$this->players()->whereNull('bid')->count();
    }

    public function playerWithWinningBid()
    {
        $winning = $this->dealer();

        foreach ($this->players as $player) {
            if ($player->pivot->bid > $winning->pivot->bid) {
                $winning = $player;
            }
        }

        return $winning;
    }

    public function bidWinner()
    {
        return $this->players()->where('bid_winner', true)->first();
    }

    public function hasBidWinner()
    {
        return !!$this->playerWithWinningBid()->pivot->bid;
    }

    public function selectBidWinner()
    {
        $this->players()->updateExistingPivot(
            $this->playerWithWinningBid(),
            ['bid_winner' => true]
        );

        return $this;
    }

    public function compete()
    {
        $this->update(['status' => GameStatus::COMPETE]);

        $this->gamePlayers()->update([
            'suit' => null,
            'value' => null,
            'hand_winner' => false,
        ]);

        return $this;
    }

    public function receivedCardFrom(Player $player, PlayerCard $card)
    {
        DB::transaction(function () use ($player, $card) {
            $player->cardsForGame($this)->where('id', $card->id)->delete();

            $this->players()->updateExistingPivot($player, [
                'suit' => $card->suit,
                'value' => $card->value,
            ]);
        });

        return $this;
    }

    public function hasReceivedAllCards()
    {
        return !$this->players()->whereNull('suit')->count();
    }

    public function firstPlayerOfHand()
    {
        return $this->players()->where('position', $this->start_turn)->first();
    }

    public function firstCardOfHand()
    {
        $player = $this->firstPlayerOfHand();

        if ($player->pivot->suit) {
            return new Card($player->pivot->suit, $player->pivot->value);
        }

        // @todo(tamer)
        // throw new RoundHasNotStarted();
    }

    public function suit()
    {
        // The suit is determined by the player that was expected to play
        // the first card. Regardless if another player plays a card before.
        $card = $this->firstCardOfHand();
        if ($card) {
            return $card->suit();
        }
    }

    public function setTrumpSuit(string $suit)
    {
        $card = new Card($suit, 2);
        $this->update(['trump_suit' => $card->suit()]);
        return $this;
    }

    public function selectTrumpSuit()
    {
        $card = $this->firstCardOfHand();
        if (!$this->trumpSuit() && $card) {
            $this->setTrumpSuit($card->suit());
        }

        return $this;
    }

    public function trumpSuit()
    {
        return $this->trump_suit;
    }

    public function playerWithHighestCardValueFor($suit)
    {
        return $this->unorderedPlayers()->wherePivot('suit', $suit)->orderByDesc('value')->first();
    }

    public function playerWithWinningHand()
    {
        // The winner is the highest valued card of a trump suit (if played)
        // or the highest valued card of the suit of the first card played.
        return $this->playerWithHighestCardValueFor($this->trumpSuit())
            ?: $this->playerWithHighestCardValueFor($this->suit());
    }

    public function handWinner()
    {
        return $this->players()->where('hand_winner', true)->first();
    }

    public function selectHandWinner()
    {
        if (!$this->hasReceivedAllCards()) {
            // @todo(tamer) - throw exception
            // throw new PlayerHasNotPlayedCardException();
            return false;
        }

        $player = $this->playerWithWinningHand();

        $this->players()->updateExistingPivot($player, [
            'hand_winner' => true,
            'hand_wins' => $player->pivot->hand_wins + 1
        ]);

        return $this;
    }

    public function hasReceivedAllHands()
    {
        return !$this->players()->whereHas('cards', function ($query) {
            return $query->where('game_id', $this->id);
        })->count();
    }

    public function pointsForRound($team)
    {
        $bidWinners = $this->players()->where('team', $team)->where('bid_winner', true)->exists();
        $bid = (int) $this->players()->where('team', $team)->where('bid_winner', true)->limit(1)->pluck('bid')->first();
        $handWins = (int) $this->players()->where('team', $team)->sum('hand_wins');

        // The team who won the bidding must win hands for the number of bids
        // they selected or they will lose those points. If the non bidding team
        // has won 7 more hands, they will have stolen the points and receive
        // points for their hands.
        if ($bidWinners) {
            return $handWins >= $bid ? $handWins : -$bid;
        } else {
            return $handWins >= 7 ? $handWins : 0;
        }
    }

    public function homeTeamPointsForRound()
    {
        return $this->pointsForRound(static::HOME_TEAM);
    }

    public function guestTeamPointsForRound()
    {
        return $this->pointsForRound(static::GUEST_TEAM);
    }

    public function selectRoundWinner()
    {
        DB::transaction(function () {
            if ($this->hasBidWinner()) {
                $bidWinner = $this->bidWinner();
                $homeTeamPoints = $this->homeTeamPointsForRound();
                $guestTeamPoints = $this->guestTeamPointsForRound();
                $this->rounds()->create([
                    'bid_winner' => $bidWinner->id,
                    'bid' => $bidWinner->pivot->bid,
                    'home_team_points' => $homeTeamPoints,
                    'guest_team_points' => $guestTeamPoints,
                ]);

                $this->update([
                    'home_team_points' => $this->home_team_points + $homeTeamPoints,
                    'guest_team_points' => $this->guest_team_points + $guestTeamPoints,
                ]);
            }
        });

        return $this;
    }

    public function hasWinner()
    {
        return $this->home_team_points >= $this->pointsToWin()
            || $this->guest_team_points >= $this->pointsToWin();
    }

    public function selectGameWinner()
    {
        $this->update(['status' => GameStatus::WINNER]);
        return $this;
    }

    public function winningTeamName()
    {
        if ($this->home_team_points >= $this->pointsToWin()) {
            return static::HOME_TEAM_NAME;
        } else if ($this->away_team_points >= $this->pointsToWin()) {
            return static::GUEST_TEAM_NAME;
        }
    }

    public function hasTeam($team)
    {
        return $team === static::HOME_TEAM || $team === static::GUEST_TEAM;
    }

    public function withLazyLoads()
    {
        return $this->load(['players']);
    }

    public function channel()
    {
        return 'game.' . $this->id;
    }

    public function toBroadcast()
    {
        return new GameResource($this->fresh()->withLazyLoads());
    }
}
