<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Computer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ComputerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $computer = $this->route('computer');

        return $computer instanceof Computer
            ? Gate::allows('update', $computer)
            : Gate::allows('create', Computer::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80', Rule::unique('computers')->ignore($this->route('computer'))],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
