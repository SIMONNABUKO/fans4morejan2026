<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BlockLocationRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'country_code' => 'required|string|size:2',
            'country_name' => 'required|string|max:100',
            'location_type' => 'nullable|string|in:country,region,city',
            'region_name' => 'nullable|string|max:100',
            'city_name' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'display_name' => 'nullable|string|max:255',
        ];
    }
}

