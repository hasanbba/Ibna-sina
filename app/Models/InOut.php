<?php

namespace App\Models;

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

    public function consultant()
    {
        return $this->belongsTo(Consultant::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
