<?php

namespace App\Http\Requests\Bets;

use Illuminate\Foundation\Http\FormRequest;

class PlaceBetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:straight,red,black,odd,even,dozen'],
            'selection' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:1'],
        ];
    }
}
