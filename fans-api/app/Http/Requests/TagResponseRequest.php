<?php
// app/Http/Requests/TagResponseRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TagResponseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'tag_id' => 'required|exists:post_tags,id',
            'response' => 'required|in:approve,reject'
        ];
    }
}