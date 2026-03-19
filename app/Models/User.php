<?php

namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function consultants(): BelongsToMany
    {
        return $this->belongsToMany(
            Consultant::class,
            'doctor_assignments',
            'user_id',
            'consultant_id'
        )->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function assignedConsultantIds(): array
    {
        return $this->consultants()
            ->pluck('consultants.id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
    }
}
