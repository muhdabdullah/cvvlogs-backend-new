<?php

namespace App\Http\Requests\Admin;

class DashboardStatsRequest extends BaseAPIRequest
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
        return [
            'start_date' => 'required_with:end_date|date_format:Y-m-d',
            'end_date'   => 'required_with:start_date|date_format:Y-m-d|after_or_equal:start_date'
        ];
    }
}
