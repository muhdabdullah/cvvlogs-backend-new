<?php

namespace App\Http\Requests\Admin;

use App\Models\UserVideo;

class UpdateUserVideoRequest extends BaseAPIRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return UserVideo::$updateStatusRule;
    }
}
