<?php

namespace App\Events;

use App\Game;
use App\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class GameEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $game;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Game $game)
    {
        $this->game = $game;

        $this->record();
        $this->announce();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel($this->game->channel());
    }

    public function broadcastWith()
    {
        return [
            'game' => $this->game->toBroadcast()
        ];
    }

    public function record()
    {
        $class = explode('\\', get_called_class());
        $name = array_pop($class);

        $this->game->update(['event' => $name]);
        $this->game->events()->create([
            'name' => $name,
            'data' => $this->game->toArray()
        ]);
    }

    protected function announce()
    {
        if ($this instanceof Announceable) {
            $message = Message::create(array_merge(['game_id' => $this->game->id], $this->message()));

            event(new MessageWasCreated($message));
        }
    }
}
