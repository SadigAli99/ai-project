<?php

namespace App\Http\Requests\Live;

use Illuminate\Foundation\Http\FormRequest;

class RespondRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'audio' => ['required', 'file', 'mimes:webm,wav,mp3,m4a,ogg', 'max:10240'],
            'history' => ['nullable', 'json'],
        ];
    }
}
