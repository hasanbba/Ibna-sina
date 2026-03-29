<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class InOut extends Model
{
    protected $table = 'in_outs';

    protected $fillable = [
        'consultant_id',
        'department_id',
        'date',
        'in_time',
        'out_time',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function setInTimeAttribute($value): void
    {
        $this->attributes['in_time'] = $this->normalizeTime($value);
    }

    public function setOutTimeAttribute($value): void
    {
        $this->attributes['out_time'] = $this->normalizeTime($value);
    }

    public function consultant()
    {
        return $this->belongsTo(Consultant::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    protected function normalizeTime(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        foreach (['H:i:s', 'H:i', 'h:i A'] as $format) {
            try {
                return Carbon::createFromFormat($format, (string) $value)->format('H:i:s');
            } catch (\Throwable $exception) {
                continue;
            }
        }

        return $value;
    }
}
