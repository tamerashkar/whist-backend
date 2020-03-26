<?php

namespace App\Http\Requests;

use App\Rules\EmptyPosition;
use App\Rules\FreeAgent;
use App\Rules\TeamExists;
use Illuminate\Foundation\Http\FormRequest;

class JoinGameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'team' => [
                'required',
                new TeamExists($this->game),
                new EmptyPosition($this->game),
                new FreeAgent($this->game, $this->user()->player),
            ]
        ];
    }
}
