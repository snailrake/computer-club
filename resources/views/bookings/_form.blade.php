@php
    $minimumTime = now(config('app.club_timezone'))->addMinute()->startOfMinute()->format(App\Models\Booking::INPUT_DATE_FORMAT);
@endphp
<div class="field">
    <label for="computer_id">Компьютер</label>
    <select id="computer_id" name="computer_id" required>
        <option value="">Выберите компьютер</option>
        @foreach ($computers as $computer)
            <option value="{{ $computer->id }}" @selected((string) old('computer_id', $selectedComputerId) === (string) $computer->id)>{{ $computer->name }}</option>
        @endforeach
    </select>
</div>
@if ($booking->exists && ! $booking->computer->is_active)
    <p class="muted">Текущий компьютер отключён. Выберите другой компьютер.</p>
@endif
<div class="field-row">
    <div class="field">
        <label for="starts_at">Начало</label>
        <input id="starts_at" name="starts_at" type="datetime-local" min="{{ $minimumTime }}" required
               value="{{ old('starts_at', $booking->exists ? $booking->startsAtInClubTime()->format(App\Models\Booking::INPUT_DATE_FORMAT) : null) }}"
               aria-describedby="time-hint">
    </div>
    <div class="field">
        <label for="ends_at">Окончание</label>
        <input id="ends_at" name="ends_at" type="datetime-local" min="{{ $minimumTime }}" required
               value="{{ old('ends_at', $booking->exists ? $booking->endsAtInClubTime()->format(App\Models\Booking::INPUT_DATE_FORMAT) : null) }}"
               aria-describedby="time-hint">
    </div>
</div>
<p id="time-hint" class="muted">Время по Саратову (UTC+4). Начало — в будущем, окончание — позже начала.</p>
