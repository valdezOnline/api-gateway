<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiServiceProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_key',
        'display_name',
        'description',
        'enabled',
        'meta',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'meta' => 'array',
    ];

    public function applications()
    {
        return $this->belongsToMany(Application::class, 'application_api_service_provider')
            ->withPivot(['enabled', 'assigned_by_user_id'])
            ->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_api_service_provider')
            ->withPivot(['enabled', 'assigned_by_user_id'])
            ->withTimestamps();
    }
}
