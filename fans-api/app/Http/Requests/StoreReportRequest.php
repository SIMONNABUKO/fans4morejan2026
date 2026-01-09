<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Report;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        \Log::info('🔴 StoreReportRequest::authorize called', [
            'user_id' => auth()->id()
        ]);
        return true; // Add any authorization logic if needed
    }
    
    protected function prepareForValidation()
    {
        \Log::info('🔴 StoreReportRequest::prepareForValidation called', [
            'request_data' => $this->all()
        ]);
    }

    public function rules(): array
    {
        return [
            'content_type' => ['required', 'string', 'in:post,comment,media,user'],
            'content_id' => ['required', 'integer'],
            'reason' => ['required', 'string', 'in:' . implode(',', [
                Report::REASON_TOS,
                Report::REASON_DMCA,
                Report::REASON_SPAM,
                Report::REASON_ABUSE,
            ])],
            'additional_info' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'content_type.in' => 'Invalid content type specified.',
            'reason.in' => 'Invalid report reason specified.',
            'additional_info.max' => 'Additional information must not exceed 1000 characters.',
        ];
    }
}