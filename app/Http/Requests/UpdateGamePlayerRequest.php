<?php

namespace App\Http\Requests;

use App\Rules\Turn;
use App\Rules\ValidBid;
use App\Rules\TimeToBid;
use App\Rules\ValidCard;
use App\Rules\MissingBid;
use App\Rules\MissingCard;
use App\Rules\TimeToPlayCard;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGamePlayerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->player->id === $this->user()->player->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'bid' => [
                'nullable',
                new TimeToBid($this->game),
                new Turn($this->game, $this->user()->player),
                new MissingBid($this->game, $this->user()->player),
                new ValidBid($this->game, $this->user()->player),
            ],
            'card' => [
                'nullable',
                new TimeToPlayCard($this->game),
                new Turn($this->game, $this->user()->player),
                new MissingCard($this->game, $this->user()->player),
                new ValidCard($this->game, $this->user()->player),
            ]
        ];
    }
}
