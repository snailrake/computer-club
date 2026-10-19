<div class="field">
    <label for="name">Название компьютера</label>
    <input id="name" name="name" value="{{ old('name', $computer->name) }}" maxlength="80" required>
</div>
<div class="field">
    <label for="description">Описание</label>
    <textarea id="description" name="description" rows="4" maxlength="2000">{{ old('description', $computer->description) }}</textarea>
</div>
<label class="checkbox">
    <input type="hidden" name="is_active" value="0">
    <input name="is_active" type="checkbox" value="1" @checked(old('is_active', $computer->is_active))>
    Принимать новые бронирования
</label>
