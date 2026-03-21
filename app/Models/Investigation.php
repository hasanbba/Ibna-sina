<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investigation extends Model
{
    protected $fillable = [
        'month',
        'consultant_id',
        'department_id',
        'investigation_id',
        'amount',
    ];

    protected $casts = [
        'month' => 'date',
        'amount' => 'decimal:2',
    ];

    public function consultant()
    {
        return $this->belongsTo(Consultant::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
