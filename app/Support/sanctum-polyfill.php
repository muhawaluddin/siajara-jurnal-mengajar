<?php

namespace Laravel\Sanctum;

if (trait_exists(HasApiTokens::class)) {
    return;
}

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Sanctum
{
    /**
     * The personal access token model class name.
     */
    public static string $personalAccessTokenModel = PersonalAccessToken::class;

    /**
     * Specify the personal access token model class name.
     */
    public static function usePersonalAccessTokenModel(string $model): void
    {
        static::$personalAccessTokenModel = $model;
    }
}

class NewAccessToken
{
    public function __construct(
        public PersonalAccessToken $accessToken,
        public string $plainTextToken,
    ) {
    }

    public function toArray(): array
    {
        return [
            'accessToken' => $this->accessToken,
            'plainTextToken' => $this->plainTextToken,
        ];
    }
}

class PersonalAccessToken extends Model
{
    protected $table = 'personal_access_tokens';

    protected $guarded = [];

    protected $casts = [
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }

    public function can(string $ability): bool
    {
        $abilities = $this->abilities ?? [];

        if (in_array('*', $abilities, true)) {
            return true;
        }

        return in_array($ability, $abilities, true);
    }

    public function cant(string $ability): bool
    {
        return ! $this->can($ability);
    }

    public function canAny(array $abilities): bool
    {
        foreach ($abilities as $ability) {
            if ($this->can($ability)) {
                return true;
            }
        }

        return false;
    }

    public function canAll(array $abilities): bool
    {
        foreach ($abilities as $ability) {
            if (! $this->can($ability)) {
                return false;
            }
        }

        return true;
    }

    public function refreshLastUsedAt(): void
    {
        $this->forceFill(['last_used_at' => now()])->save();
    }

    public function hasExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->lte(now());
    }

    /**
     * Scope to include only valid tokens.
     */
    public function scopeValid($query): void
    {
        $query->where(function ($query) {
            $query->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }
}

trait HasApiTokens
{
    public PersonalAccessToken|null $accessToken = null;

    public function tokens(): MorphMany
    {
        $model = Sanctum::$personalAccessTokenModel;

        return $this->morphMany($model, 'tokenable');
    }

    public function createToken(string $name, array $abilities = ['*'], $expiresAt = null): NewAccessToken
    {
        $plainTextToken = Str::random(40);

        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
        ]);

        return new NewAccessToken($token, $token->getKey().'|'.$plainTextToken);
    }

    public function withAccessToken(PersonalAccessToken $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function currentAccessToken(): ?PersonalAccessToken
    {
        return $this->accessToken;
    }

    public function tokenCan(string $ability): bool
    {
        return $this->accessToken?->can($ability) ?? false;
    }

    public function tokenCanAny(array $abilities): bool
    {
        return $this->accessToken?->canAny($abilities) ?? false;
    }

    public function tokenCanAll(array $abilities): bool
    {
        return $this->accessToken?->canAll($abilities) ?? false;
    }

    public function hasValidToken(): bool
    {
        return $this->tokens()->valid()->exists();
    }

    public function withValidToken(): ?static
    {
        $token = $this->tokens()->valid()->first();

        if ($token === null) {
            return null;
        }

        return $this->withAccessToken($token);
    }
}
