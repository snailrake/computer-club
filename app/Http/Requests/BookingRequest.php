<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        $booking = $this->route('booking');

        return $booking === null || Gate::allows('update', $booking);
    }

    public function rules(): array
    {
        return [
            'computer_id' => ['bail', 'required', 'integer', Rule::exists('computers', 'id')->where('is_active', true)],
            'starts_at' => ['bail', 'required', 'date_format:'.Booking::INPUT_DATE_FORMAT],
            'ends_at' => ['required', 'date_format:'.Booking::INPUT_DATE_FORMAT, 'after:starts_at'],
            'user_id' => ['prohibited'],
            'role' => ['prohibited'],
        ];
    }
}
