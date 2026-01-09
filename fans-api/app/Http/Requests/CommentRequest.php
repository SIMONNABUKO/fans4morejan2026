<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'content' => 'required|string|max:1000',
            'media_url' => 'nullable|string|max:2048',
            'media' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,mov,webm|max:20480',
            'scheduled_for' => 'nullable|date',
            'delete_at' => 'nullable|date',
        ];
    }
}

