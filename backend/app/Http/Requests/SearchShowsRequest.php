<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchShowsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            // No minimum: TVMaze accepts one character, and shows like "X" or "V" need it.
            'q' => ['required', 'string', 'max:100'],
        ];
    }
}
