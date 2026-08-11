<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEarthquakeSubscriberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'min_magnitude' => ['required', 'numeric', 'between:0,9.9'],
            'department' => ['nullable', 'string', 'max:100'],
        ];
    }
}
