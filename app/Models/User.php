<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\HasPropertyRoles;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasPropertyRoles, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'current_property_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

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
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Get all properties that the user has access to (many-to-many)
     */
    public function properties()
    {
        return $this->belongsToMany(Property::class)
            ->withTimestamps()
            ->select('properties.*');
    }

    /**
     * Get the user's currently selected property
     */
    public function currentProperty()
    {
        return $this->belongsTo(Property::class, 'current_property_id');
    }

    /**
     * Switch to a different property
     */
    public function switchProperty(Property $property): bool
    {
        if (! $this->hasProperty($property)) {
            return false;
        }

        $this->forceFill([
            'current_property_id' => $property->id,
        ])->save();

        return true;
    }

    /**
     * Check if user has access to a property
     */
    public function hasProperty(Property $property): bool
    {
        return $this->properties()->where('properties.id', $property->id)->exists();
    }

    public function hasEnabledTwoFactorAuthentication(): bool
    {
        return !is_null($this->two_factor_secret) && !is_null($this->two_factor_confirmed_at);
    }
}
