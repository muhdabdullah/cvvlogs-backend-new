<?php

namespace App\Http\Requests\Admin;

use App\Models\Job;

class MarkUnMarkJobFavRequest extends BaseAPIRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'job_id' => 'required|exists:job,id',
            'user_id' => 'required|int',
            'is_fav' => 'required|between:0,1',
        ];
    }
}
