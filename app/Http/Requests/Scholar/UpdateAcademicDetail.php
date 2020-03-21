<?php

namespace App\Http\Requests\Scholar;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademicDetail extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $indexedIn = implode(',', array_keys(config('options.scholars.academic_details.indexed_in')));

        return [
            'authors' => ['sometimes', 'required', 'array', 'max:10', 'min:1'],
            'authors.0' => ['sometimes', 'required', 'string'],
            'title' => ['sometimes', 'required', 'string', 'max:400'],
            'conference' => ['sometimes', 'required', 'string', 'max:100'],
            'volume' => ['nullable', 'integer'],
            'publisher' => ['sometimes', 'required', 'string', 'max:100'],
            'page_numbers' => ['sometimes', 'required', 'array', 'size:2'],
            'page_numbers.to' => ['required', 'integer'],
            'page_numbers.from' => ['required', 'integer', 'lte:page_numbers.to'],
            'date' => ['sometimes', 'required', 'date'],
            'number' => ['nullable', 'numeric'],
            'venue' => ['sometimes', 'required', 'array', 'size:2'],
            'venue.city' => ['required', 'string'],
            'venue.country' => ['required', 'string'],
            'indexed_in' => ['sometimes', 'required', 'array'],
            'indexed_in.*' => ['in:' . $indexedIn],
        ];
    }
}