<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyReportSummary extends Model
{
    protected $fillable = [
        'year',
        'month',
        'room',
        'consultant',
        'occupied',
    ];
}
