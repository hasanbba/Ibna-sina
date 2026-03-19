<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class consultant extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasRoles;
    protected $fillable = ['department_id', 'name', 'status'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'doctor_assignments',
            'consultant_id',
            'user_id'
        )->withTimestamps();
    }
}
