<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;

class CreatorApplicationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // Detailed logging of the request data
        Log::info('CreatorApplicationRequest - Full request data:', [
            'all' => $this->all(),
            'files' => $this->allFiles(),
            'back_id' => $this->input('back_id')
        ]);
    }

    public function rules()
    {
        $mediaRules = function($field) {
            return [
                'required',
                function ($attribute, $value, $fail) {
                    Log::info("Validating {$attribute}", [
                        'value' => $value,
                        'is_file' => $this->hasFile($attribute),
                        'is_string' => is_string($value)
                    ]);

                    // If it's null or empty string, fail early
                    if ($value === null || (is_string($value) && trim($value) === '')) {
                        $fail("The {$attribute} is required.");
                        return;
                    }

                    // If it's a file upload
                    if ($this->hasFile($attribute)) {
                        $file = $this->file($attribute);
                        $extension = strtolower($file->getClientOriginalExtension());
                        
                        if (!in_array($extension, ['jpeg', 'jpg', 'png'])) {
                            $fail("The {$attribute} must be a valid image file (jpeg or png).");
                        }
                        
                        if ($file->getSize() > 7168 * 1024) {
                            $fail("The {$attribute} must not be larger than 7MB.");
                        }
                        return;
                    }

                    // If it's a string (URL)
                    if (is_string($value)) {
                        // Accept any non-empty string for now to debug
                        if (trim($value) === '') {
                            $fail("The {$attribute} cannot be empty.");
                        }
                        return;
                    }

                    // If we get here, the value is neither a file nor a string
                    $fail("The {$attribute} must be either a file or a URL.");
                }
            ];
        };

        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birthday' => 'required|date',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip_code' => 'required|string|max:20',
            'document_type' => 'required|string|in:driving_license,state_id',
            'front_id' => $mediaRules('front_id'),
            'back_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    Log::info("Validating back_id", [
                        'value' => $value,
                        'is_file' => $this->hasFile($attribute),
                        'is_string' => is_string($value),
                        'is_null' => is_null($value)
                    ]);

                    // If the value is null or empty string, it's valid
                    if ($value === null || (is_string($value) && trim($value) === '')) {
                        return;
                    }

                    // If it's a file upload
                    if ($this->hasFile($attribute)) {
                        $file = $this->file($attribute);
                        $extension = strtolower($file->getClientOriginalExtension());
                        
                        if (!in_array($extension, ['jpeg', 'jpg', 'png'])) {
                            $fail("The {$attribute} must be a valid image file (jpeg or png).");
                        }
                        
                        if ($file->getSize() > 7168 * 1024) {
                            $fail("The {$attribute} must not be larger than 7MB.");
                        }
                        return;
                    }

                    // If it's a string (URL)
                    if (is_string($value)) {
                        // Accept any non-empty string for now to debug
                        if (trim($value) === '') {
                            $fail("The {$attribute} cannot be empty.");
                        }
                        return;
                    }

                    // If we get here, the value is neither null, a file, nor a string
                    $fail("The {$attribute} must be either a file or a URL.");
                }
            ],
            'holding_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    // If the value is null or empty string, it's valid
                    if ($value === null || (is_string($value) && trim($value) === '')) {
                        return;
                    }

                    // If it's a file upload
                    if ($this->hasFile($attribute)) {
                        $file = $this->file($attribute);
                        $extension = strtolower($file->getClientOriginalExtension());
                        
                        if (!in_array($extension, ['jpeg', 'jpg', 'png'])) {
                            $fail("The {$attribute} must be a valid image file (jpeg or png).");
                        }
                        
                        if ($file->getSize() > 7168 * 1024) {
                            $fail("The {$attribute} must not be larger than 7MB.");
                        }
                        return;
                    }

                    // If it's a string (URL)
                    if (is_string($value)) {
                        if (trim($value) === '') {
                            $fail("The {$attribute} cannot be empty.");
                        }
                        return;
                    }

                    // If we get here, the value is neither null, a file, nor a string
                    $fail("The {$attribute} must be either a file or a URL.");
                }
            ],
        ];
    }

    public function messages()
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'birthday.required' => 'Birthday is required.',
            'birthday.date' => 'Birthday must be a valid date.',
            'address.required' => 'Address is required.',
            'city.required' => 'City is required.',
            'country.required' => 'Country is required.',
            'state.required' => 'State is required.',
            'zip_code.required' => 'Zip code is required.',
            'document_type.required' => 'Document type is required.',
            'document_type.in' => 'Invalid document type selected.',
            'front_id.required' => 'Front ID image is required.',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // Log validation errors with request data
        Log::error('CreatorApplicationRequest validation failed:', [
            'errors' => $validator->errors()->toArray(),
            'request_data' => $this->all(),
            'files' => $this->allFiles()
        ]);

        parent::failedValidation($validator);
    }
}