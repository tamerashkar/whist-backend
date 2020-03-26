<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
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
            'event' => $this->event,
            'host' => $this->whenLoaded('players', function () {
                return $this->players()->count() ? (string) $this->players->first()->id : null;
            }),
            'turn' => $this->next_turn,
            'trumpSuit' => $this->trumpSuit(),
            'status' => $this->status()->name,
            'homeTeamPoints' => $this->home_team_points,
            'guestTeamPoints' => $this->guest_team_points,
            'dealer' => $this->hasDealer() ? $this->dealer()->id : null,
            'players' => $this->whenLoaded('players', function () {
                return PlayerResource::collection($this->players);
            }),
        ];
    }
}
