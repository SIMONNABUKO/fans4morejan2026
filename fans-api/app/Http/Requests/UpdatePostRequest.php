<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class UpdatePostRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'content' => 'sometimes|nullable|string|max:1000',
            'media' => 'sometimes|array',
            'media.*.id' => 'nullable',
            'media.*.url' => 'nullable|string',
            'media.*.file' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4|max:20480',
            'media.*.type' => ['required_with:media', Rule::in(['image', 'video'])],
            'media.*.previewVersions' => 'nullable|array',
            'media.*.previewVersions.*' => 'nullable|file|mimes:jpeg,png,jpg,mp4,gif|max:20480',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'required|array',
            'permissions.*.*' => 'required|array',
            'permissions.*.*.type' => [
                'required',
                Rule::in(['subscribed_all_tiers', 'add_price', 'limited_time', 'following'])
            ],
            'permissions.*.*.value' => 'nullable',
            'tagged_users' => 'sometimes|array',
            'tagged_users.*' => 'exists:users,id',
            'scheduled_for' => 'nullable|date',
            'expires_at' => 'nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'media.*.type.required_with' => 'Each media item must specify a type.',
            'media.*.type.in' => 'The media type must be either image or video.',
            'media.*.file.mimes' => 'The media file must be a file of type: jpeg, png, jpg, gif, mp4.',
            'media.*.file.max' => 'The media file may not be greater than 20MB.',
            'media.*.previewVersions.*.mimes' => 'The preview file must be a file of type: jpeg, png, jpg, gif.',
            'media.*.previewVersions.*.max' => 'The preview file may not be greater than 5MB.',
            'permissions.array' => 'The permissions must be an array.',
            'permissions.*.*.type.in' => 'The permission type must be one of: subscribed_all_tiers, add_price, limited_time, or following.',
        ];
    }

    protected function prepareForValidation()
    {
        // Only decode permissions if it's a string
        $permissions = $this->get('permissions');
        if (is_string($permissions)) {
            $this->merge([
                'permissions' => json_decode($permissions, true)
            ]);
        }

        // Clean up media data
        if ($this->has('media')) {
            $media = collect($this->get('media'))->map(function ($item, $index) {
                // Map previewVersions to previews for backend compatibility
                if (isset($item['previewVersions'])) {
                    $item['previews'] = array_map(function ($preview) {
                        return is_array($preview) && isset($preview['url']) ? $preview['url'] : $preview;
                    }, $item['previewVersions']);
                    unset($item['previewVersions']);
                }
                // Handle preview versions (legacy logic)
                if (isset($item['previews'])) {
                    $previewVersions = [];
                    foreach ($item['previews'] as $previewIndex => $preview) {
                        if ($preview instanceof \Illuminate\Http\UploadedFile) {
                            $previewVersions[] = $preview;
                        }
                    }
                    $item['previews'] = $previewVersions;
                }
                return $item;
            })->all();

            $this->merge(['media' => $media]);
        }
    }
} 