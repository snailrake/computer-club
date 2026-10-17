<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['computer_id', 'starts_at', 'ends_at'])]
class Booking extends Model
{
    public const INPUT_DATE_FORMAT = 'Y-m-d\TH:i';

    public function startsAtInClubTime(): CarbonImmutable
    {
        return $this->starts_at->setTimezone(config('app.club_timezone'));
    }

    public function endsAtInClubTime(): CarbonImmutable
    {
        return $this->ends_at->setTimezone(config('app.club_timezone'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function computer(): BelongsTo
    {
        return $this->belongsTo(Computer::class);
    }

    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
        ];
    }
}
