<?php
// app/Http/Requests/TagUserRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TagUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'post_id' => 'required|exists:posts,id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ];
    }
}