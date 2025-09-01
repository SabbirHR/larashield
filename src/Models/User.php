<?php
namespace Larashield\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    protected $fillable = ['name','email','password','phone','gender','user_type','status','email_verified_at'];
    protected $hidden = ['password','remember_token'];
}
