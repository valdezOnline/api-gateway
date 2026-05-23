<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Http\Filters\v1\QueryFilter;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function scopeFilter(Builder $builder, QueryFilter $filters)
    {
        return $filters->apply($builder);
    }

    public function apiServiceProviders()
    {
        return $this->belongsToMany(ApiServiceProvider::class, 'user_api_service_provider')
            ->withPivot(['enabled', 'assigned_by_user_id'])
            ->withTimestamps();
    }

    public function hasEnabledServiceAccess(string $serviceKey): bool
    {
        return $this->apiServiceProviders()
            ->where('service_key', $serviceKey)
            ->wherePivot('enabled', true)
            ->exists();
    }
}
