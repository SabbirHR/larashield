<?php

namespace Larashield\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Larashield\Traits\CustomAuditable;
use Larashield\Traits\SerializesDateWithTimestamp;
use Laravel\Sanctum\HasApiTokens;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable implements Auditable
{
    use HasApiTokens, Notifiable, HasRoles, CustomAuditable, SerializesDateWithTimestamp;
    // Audit log hidden password
    protected $auditExclude = ['password'];
    protected $fillable = ['name', 'email', 'password', 'phone', 'gender', 'user_type', 'status', 'email_verified_at'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public $allowedFields = [
        'id',
        'name',
        'email',
        'phone',
        'gender',
        'image',
        'status',
        'email_verified_at',
        'roles.id',
        'roles.name',
        'created_at',
        'updated_at'

    ];
    public $allowedFilters = [
        'id',
        'name',
        'email',
        'phone',
        'gender',
        'image',
        'status',
        'email_verified_at',
        'roles.id',
        'roles.name',
        'created_at',
        'updated_at'

    ];
    public $allowedSorts = [
        'id',
        'name',
        'email',
        'phone',
        'gender',
        'status',
        'email_verified_at',
        'roles.id',
        'roles.name',
        'created_at',
        'updated_at'
    ];
    public $allowedIncludes = [
        'roles',
        'permission'
    ];
}
