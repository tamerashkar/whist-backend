<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'robot' => (bool) $this->robot,
            $this->mergeWhen($this->pivot, function () {
                return [
                    'bid' => $this->pivot->bid,
                    'team' => (int) $this->pivot->team,
                    'card' => $this->pivot->suit ? [
                        'suit' => $this->pivot->suit,
                        'value' => (int) $this->pivot->value,
                    ] : null,
                    'turn' => (int) $this->pivot->position,
                    'bidWinner' => (bool) $this->pivot->bid_winner,
                    'handWinner' => (bool) $this->pivot->hand_winner,
                    'handWins' => (int) $this->pivot->hand_wins
                ];
            }),
        ];
    }
}
