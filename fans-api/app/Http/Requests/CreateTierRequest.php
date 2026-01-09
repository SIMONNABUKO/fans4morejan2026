<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Tier;

class CreateTierRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('create', Tier::class);
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'color_code' => 'required|string|max:7',
            'subscription_benefits' => 'required|array',
            'subscription_benefits.*' => 'string',
            'base_price' => 'required|numeric|min:0',
            'two_month_price' => 'nullable|numeric|min:0',
            'three_month_price' => 'nullable|numeric|min:0',
            'six_month_price' => 'nullable|numeric|min:0',
            'two_month_discount' => 'integer|min:0|max:100',
            'three_month_discount' => 'integer|min:0|max:100',
            'six_month_discount' => 'integer|min:0|max:100',
            'active_plans' => 'required|array',
            'active_plans.*' => 'integer|in:1,2,3,6',
            'subscriptions_enabled' => 'boolean',
            'description' => 'nullable|string',
            'max_subscribers' => 'nullable|integer|min:1',
            'max_subscribers_enabled' => 'boolean',
        ];
    }
}

