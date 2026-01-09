<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreatePostRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'content' => 'required_without:media|nullable|string|max:1000',
            'media' => 'required_without:content|nullable|array',
            'media.*.id' => 'nullable',
            'media.*.url' => 'nullable|string',
            'media.*.file' => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4|max:20480',
            'media.*.type' => ['required_with:media', Rule::in(['image', 'video'])],
            'media.*.previewVersions' => 'nullable|array',
            'media.*.previewVersions.*' => 'nullable|file|mimes:jpeg,png,jpg,mp4,gif|max:20480',
            'permissions' => 'present|array',
            'permissions.*' => 'array',
            'permissions.*.*' => 'array',
            'permissions.*.*.type' => [
                'required_with:permissions.*.*',
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
            'content.required_without' => 'The content field is required when no media is present.',
            'media.required_without' => 'The media field is required when no content is present.',
            'media.*.type.required_with' => 'Each media item must specify a type.',
            'media.*.type.in' => 'The media type must be either image or video.',
            'media.*.file.mimes' => 'The media file must be a file of type: jpeg, png, jpg, gif, mp4.',
            'media.*.file.max' => 'The media file may not be greater than 20MB.',
            'media.*.previewVersions.*.mimes' => 'The preview file must be a file of type: jpeg, png, jpg, gif.',
            'media.*.previewVersions.*.max' => 'The preview file may not be greater than 5MB.',
            'permissions.required' => 'The permissions field is required.',
            'permissions.array' => 'The permissions must be an array.',
            'permissions.*.*.type.in' => 'The permission type must be one of: subscribed_all_tiers, add_price, limited_time, or following.',
        ];
    }

    protected function prepareForValidation()
    {
        Log::info('Preparing for validation', ['request_data' => $this->all()]);

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
                // Log the media item being processed
                Log::info('Processing media item', [
                    'index' => $index,
                    'item' => array_keys($item)
                ]);

                // Handle the main file
                if (isset($item['file']) && $item['file'] instanceof \Illuminate\Http\UploadedFile) {
                    Log::info('Media file received', [
                        'index' => $index,
                        'original_name' => $item['file']->getClientOriginalName(),
                        'size' => $item['file']->getSize(),
                        'mime_type' => $item['file']->getMimeType()
                    ]);
                }

                // Handle preview versions
                if (isset($item['previewVersions'])) {
                    $previewVersions = [];
                    foreach ($item['previewVersions'] as $previewIndex => $preview) {
                        if ($preview instanceof \Illuminate\Http\UploadedFile) {
                            Log::info('Preview file received', [
                                'media_index' => $index,
                                'preview_index' => $previewIndex,
                                'original_name' => $preview->getClientOriginalName(),
                                'size' => $preview->getSize(),
                                'mime_type' => $preview->getMimeType()
                            ]);
                            $previewVersions[] = $preview;
                        }
                    }
                    $item['previewVersions'] = $previewVersions;
                }

                return $item;
            })->all();

            $this->merge(['media' => $media]);
        } else {
            Log::info('No media data received in the request');
        }

        Log::info('Prepared for validation', ['prepared_data' => $this->all()]);
    }

    public function failedValidation(Validator $validator)
    {
        \Log::error('CreatePostRequest validation failed', [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->all()
        ]);
        parent::failedValidation($validator);
    }
}

