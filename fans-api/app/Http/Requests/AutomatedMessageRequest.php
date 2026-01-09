<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AutomatedMessageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'trigger' => 'required|string|in:new_follower,new_subscriber,tip_received,media_purchased',
            'content' => 'required|string|max:1000',
            'sent_delay' => 'nullable|integer|min:0',
            'cooldown' => 'nullable|integer|min:0',
            'permissions' => 'nullable|string',
            'media' => 'nullable|array',
            'media.*.file' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mov|max:102400', // 100MB max
            'media.*.type' => 'nullable|string',
            'media.*.previewVersions.*' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:102400',
        ];
    }
}