<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // optional
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject /*, MustVerifyEmail*/
{
    use HasFactory, Notifiable;

    // ---- Role constants (single-role design) ----
    public const ROLE_ADMIN      = 'Admin';
    public const ROLE_ARTIST     = 'Artist';
    public const ROLE_VENUE      = 'Venue';
    public const ROLE_JOURNALIST = 'Journalist';
    public const ROLE_FAN        = 'Fan';

    // ---- Fillable ----
    // ---- Fillable ----
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'remember_token',
    ];

    // ---- Hidden / Casts ----
    protected $hidden = ['password', 'remember_token'];

    // ---- relation to artist ----
    // ---- relation to artist ----
    public function artist()
    {
        return $this->hasOne(Artist::class);
    }

    // ---- relation to Journalist ----
    // ---- relation to Journalist ----
    public function journalist()
    {
        return $this->hasOne(Journalist::class);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ---- JWT Subject ----
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }

    // ---- Relations (use as needed) ----
    // public function artistProfile()
    // {
    //     return $this->hasOne(ArtistProfile::class);
    // }
    // public function venueProfile()
    // {
    //     return $this->hasOne(VenueProfile::class);
    // }
    // public function journalistProfile()
    // {
    //     return $this->hasOne(JournalistProfile::class);
    // }
    // public function fanProfile()
    // {
    //     return $this->hasOne(FanProfile::class);
    // }
    // public function verifications()
    // {
    //     return $this->hasMany(Verification::class);
    // }
    // public function notifications()
    // {
    //     return $this->hasMany(Notification::class);
    // }
    // public function auditLogs()
    // {
    //     return $this->hasMany(AuditLog::class);
    // }

    // ---- Scopes / Helpers ----
    public function scopeRole($q, string $role)
    {
        return $q->where('role', $role);
    }
    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }
}
