<?php

namespace App\Models;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class Remark extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasRoles;
    
    protected $fillable = ['name'];
}
