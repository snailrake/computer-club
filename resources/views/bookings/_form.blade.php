@php
    $minimumTime = now(config('app.club_timezone'))->addMinute()->startOfMinute()->format(App\Models\Booking::INPUT_DATE_FORMAT);
@endphp
<div class="field">
    <label for="computer_id">Компьютер</label>
    <select id="computer_id" name="computer_id" required
            @error('computer_id') aria-invalid="true" aria-describedby="computer_id-error" @enderror>
        <option value="">Выберите компьютер</option>
        @foreach ($computers as $computer)
            <option value="{{ $computer->id }}" @selected((string) old('computer_id', $selectedComputerId) === (string) $computer->id)>{{ $computer->name }}</option>
        @endforeach
    </select>
    <x-field-error name="computer_id" />
</div>
@if ($booking->exists && ! $booking->computer->is_active)
    <p class="notice notice-neutral">Текущий компьютер отключён для новых броней. Для переноса выберите другой.</p>
@endif
<div class="field-row">
    <div class="field">
        <label for="starts_at">Начало визита</label>
        <input id="starts_at" name="starts_at" type="datetime-local" min="{{ $minimumTime }}" required
               value="{{ old('starts_at', $booking->exists ? $booking->startsAtInClubTime()->format(App\Models\Booking::INPUT_DATE_FORMAT) : null) }}"
               aria-describedby="time-hint @error('starts_at') starts_at-error @enderror"
               @error('starts_at') aria-invalid="true" @enderror>
        <x-field-error name="starts_at" />
    </div>
    <div class="field">
        <label for="ends_at">Окончание визита</label>
        <input id="ends_at" name="ends_at" type="datetime-local" min="{{ $minimumTime }}" required
               value="{{ old('ends_at', $booking->exists ? $booking->endsAtInClubTime()->format(App\Models\Booking::INPUT_DATE_FORMAT) : null) }}"
               aria-describedby="time-hint @error('ends_at') ends_at-error @enderror"
               @error('ends_at') aria-invalid="true" @enderror>
        <x-field-error name="ends_at" />
    </div>
</div>
<p id="time-hint" class="form-note">Все даты и время — по Саратову (UTC+4), независимо от вашего местоположения. Начало — в будущем, окончание — позже начала.</p>
