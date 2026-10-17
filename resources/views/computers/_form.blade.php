<div class="field">
    <label for="name">Название компьютера</label>
    <input id="name" name="name" value="{{ old('name', $computer->name) }}" maxlength="80" required placeholder="Например, PC-04"
           @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
    <x-field-error name="name" />
</div>
<div class="field">
    <label for="description">Описание <span class="optional">необязательно</span>
    </label>
    <textarea id="description" name="description" rows="4" maxlength="2000"
              placeholder="Процессор, видеокарта, память или особенности места"
              @error('description') aria-invalid="true" aria-describedby="description-error" @enderror>{{ old('description', $computer->description) }}</textarea>
    <x-field-error name="description" />
</div>
<div class="checkbox-field">
    <input type="hidden" name="is_active" value="0">
    <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $computer->is_active))
           @error('is_active') aria-invalid="true" aria-describedby="is_active-error" @enderror>
    <div>
        <label for="is_active">Принимать новые бронирования</label>
        <p class="muted">Отключение не удаляет существующие брони.</p>
    </div>
</div>
<x-field-error name="is_active" />
