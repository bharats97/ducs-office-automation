<?php

namespace App\Http\Requests\Publication;

use App\Types\CitationIndex;
use App\Types\PublicationType;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicationRequest extends FormRequest
{
    /**
     * Prepare data for validation
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'date' => $this['date']['month'] . ' ' . $this['date']['year'],
            'is_published' => $this->filled('is_published'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'type' => ['required', Rule::in(PublicationType::values())],
            'paper_title' => ['required', 'string', 'max:400'],
            'document' => ['required', 'file', 'mimetypes:application/pdf,image/*', 'max:200'],

            'is_published' => ['required', Rule::in([true, false])],

            'co_authors' => ['nullable', 'array', 'max:10', 'min:1'],
            'co_authors.*.name' => ['required', 'string'],
            'co_authors.*.noc' => ['required', 'file', 'max:200', 'mimeTypes:application/pdf, image/*'],

            'name' => ['exclude_if:is_published,' . false, 'required', 'string', 'max:400'],
            'date' => ['exclude_if:is_published,' . false, 'required', 'date', 'before_or_equal:today'],
            'volume' => ['exclude_if:is_published,' . false, 'nullable', 'integer'],
            'indexed_in' => ['exclude_if:is_published,' . false, 'required', 'array'],
            'indexed_in.*' => [Rule::in(CitationIndex::values())],
            'page_numbers' => ['exclude_if:is_published,' . false, 'required', 'array', 'size:2'],
            'page_numbers.0' => ['exclude_if:is_published,' . false, 'required', 'integer'],
            'page_numbers.1' => ['exclude_if:is_published,' . false, 'required', 'integer', 'gte:page_numbers.0'],
            'paper_link' => ['exclude_if:is_published,' . false, 'nullable', 'url'],
            'city' => ['exclude_if:is_published,' . false, 'required_if:type,' . PublicationType::CONFERENCE, 'nullable', 'string'],
            'country' => ['exclude_if:is_published,' . false, 'required_if:type,' . PublicationType::CONFERENCE, 'nullable', 'string'],
            'publisher' => ['exclude_if:is_published,' . false, 'required_if:type,' . PublicationType::JOURNAL, 'nullable', 'string', 'max:100'],
            'number' => ['exclude_if:is_published,' . false, 'nullable', 'numeric'],
        ];

        return $rules;
    }

    public function coAuthorsDetails()
    {
        return array_map(static function ($coAuthor) {
            return [
                'name' => $coAuthor['name'],
                'noc_path' => $coAuthor['noc']->store('/publications/co_authors_noc'),
            ];
        }, $this['co_authors'] ?? []);
    }
}