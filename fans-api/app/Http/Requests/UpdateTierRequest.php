<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTierRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('update', $this->route('tier'));
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'color_code' => 'sometimes|required|string|max:7',
            'subscription_benefits' => 'sometimes|required|array',
            'subscription_benefits.*' => 'string',
            'base_price' => 'sometimes|required|numeric|min:0',
            'two_month_price' => 'nullable|numeric|min:0',
            'three_month_price' => 'nullable|numeric|min:0',
            'six_month_price' => 'nullable|numeric|min:0',
            'two_month_discount' => 'nullable|integer|min:0|max:100',
            'three_month_discount' => 'nullable|integer|min:0|max:100',
            'six_month_discount' => 'nullable|integer|min:0|max:100',
            'active_plans' => 'sometimes|required|array',
            'active_plans.*' => 'integer|in:1,2,3,6',
            'subscriptions_enabled' => 'sometimes|boolean',
            'description' => 'nullable|string',
            'max_subscribers' => 'nullable|integer|min:1',
            'max_subscribers_enabled' => 'sometimes|boolean',
        ];
    }
}