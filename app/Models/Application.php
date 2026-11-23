<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use App\Http\Filters\v1\QueryFilter;

class Application extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    public const GATEWAY_ABILITIES = ['gateway:read', 'gateway:write'];
    public const API_KEY_MAX_VALIDITY_DAYS = 365;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'created_by',
        'status',
        'apikey',
        'api_key_rotated_at',
        'api_key_revoked_at',
        'api_key_expires_at',
        'api_key_last_used_at',
        'api_key_last_used_ip',
        'allowed_abilities',
        'assigned_services',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'apikey',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'api_key_rotated_at' => 'datetime',
        'api_key_revoked_at' => 'datetime',
        'api_key_expires_at' => 'datetime',
        'api_key_last_used_at' => 'datetime',
        'allowed_abilities' => 'array',
        'assigned_services' => 'array',
    ];

    public function scopeFilter(Builder $builder, QueryFilter $filters)
    {
        return $filters->apply($builder);
    }

    protected function apikey(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if (!is_string($value) || $value === '') {
                    return $value;
                }

                return self::looksHashedApiKey($value) ? $value : Hash::make($value);
            }
        );
    }

    public function rotateApiKey(string $plainApiKey, $expiresAt = null): void
    {
        $this->forceFill([
            'apikey' => $plainApiKey,
            'api_key_rotated_at' => now(),
            'api_key_revoked_at' => null,
            'api_key_expires_at' => $expiresAt ?? now()->addDays(self::API_KEY_MAX_VALIDITY_DAYS),
        ])->save();
    }

    public function renewApiKey($expiresAt = null): void
    {
        $this->forceFill([
            'api_key_expires_at' => $expiresAt ?? now()->addDays(self::API_KEY_MAX_VALIDITY_DAYS),
            'api_key_revoked_at' => null,
            'status' => true,
        ])->save();
    }

    public function revokeApiKey(): void
    {
        $this->forceFill([
            'api_key_revoked_at' => now(),
            'status' => false,
        ])->save();
    }

    public function isApiKeyRevoked(): bool
    {
        return $this->api_key_revoked_at !== null;
    }

    public static function looksHashedApiKey(string $value): bool
    {
        return Str::startsWith($value, ['$2y$', '$2b$', '$argon2i$', '$argon2id$']);
    }

    public static function sanitizeGatewayAbilities(array $abilities): array
    {
        $validAbilities = array_flip(self::GATEWAY_ABILITIES);
        $sanitized = [];

        foreach ($abilities as $ability) {
            if (is_string($ability) && isset($validAbilities[$ability])) {
                $sanitized[] = $ability;
            }
        }

        $unique = array_values(array_unique($sanitized));

        return $unique !== [] ? $unique : ['gateway:read'];
    }

    public function tokenAbilitiesForGateway(): array
    {
        if (!is_array($this->allowed_abilities) || $this->allowed_abilities === []) {
            return self::GATEWAY_ABILITIES;
        }

        return self::sanitizeGatewayAbilities($this->allowed_abilities);
    }
}