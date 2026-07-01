<?php

namespace App\Models;

use App\DTOs\Auth\RegisterData;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTAuth;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public static function register(RegisterData $data): self
    {
        return self::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
        ]);
    }

    public function issueToken(): string
    {
        return JWTAuth::fromUser($this);
    }

    public function issueRefreshToken(): string
    {
        $refreshTtl = config('jwt.refresh_ttl');
        $defaultTtl = config('jwt.ttl');

        assert(is_int($refreshTtl));
        assert(is_int($defaultTtl));

        JWTAuth::factory()->setTTL($refreshTtl);
        $token = JWTAuth::fromUser($this);
        JWTAuth::factory()->setTTL($defaultTtl);

        return $token;
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
