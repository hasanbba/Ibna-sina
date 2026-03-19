<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reporting extends Model
{
    protected $fillable = [
        'date',
        'consultant_id',
        'department_id',
        'new',
        'report',
        'follow_up',
        'back',
        'total',
        'remark_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function consultant()
    {
        return $this->belongsTo(Consultant::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function remark()
    {
        return $this->belongsTo(Remark::class);
    }

    protected static function booted()
    {
        static::saving(function ($reporting) {
            $reporting->total = $reporting->new + $reporting->report + $reporting->follow_up + $reporting->back;
        });
    }
}
