<?php

namespace App\Models;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravelcm\Subscriptions\Traits\HasPlanSubscriptions;

class User extends Authenticatable implements HasMedia, FilamentUser
{
    use HasFactory, Notifiable;
    use HasPanelShield;
    use HasPlanSubscriptions;
    use HasRoles;
    use InteractsWithMedia;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

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

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();
    }

    protected static function booted(): void
    {
        static::created(function ($user) {
            $user->wallet()->create();
        });
    }
    public function promoter_projects(): HasMany
    {
        return $this->hasMany(Project::class, 'promoter_id');
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function transactions(): HasMany
    {
        return $this->wallet?->transactions();
    }

    public function invoices(): MorphMany
    {
        return $this->morphMany(Invoice::class, 'invoicable');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'paid_by');
    }

    public function profits(): HasMany
    {
        return $this->hasMany(Profit::class, 'paid_to');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
