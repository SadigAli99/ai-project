<?php

namespace App\Http\Requests\Conversation;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message_type' => ['required', 'in:text,audio'],
            'title' => ['nullable', 'string', 'max:150'],
            'content' => ['nullable', 'required_if:message_type,text'],
            'audio' => ['nullable', 'required_if:message_type,audio', 'file', 'mimes:webm,wav,mp3,m4a,ogg', 'max:20480']
        ];
    }
}
