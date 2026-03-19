<?php

namespace App\Models;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class department extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasRoles;

    protected $fillable = ['name'];
    
    
    public function consultants()
    {
        return $this->hasMany(Consultant::class);
    }
}
