<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, \Illuminate\Database\Eloquent\SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'section_id',
        'is_active'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'performed_by');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }

    /**
     * Check if user is a manager
     */
    public function isManager(): bool
    {
        return $this->hasRole('Manager');
    }

    /**
     * Check if user is a chef
     */
    public function isChef(): bool
    {
        return $this->hasRole('Chef');
    }

    /**
     * Check if user is a store keeper
     */
    public function isStoreKeeper(): bool
    {
        return $this->hasRole('Store Keeper');
    }

    /**
     * Check if user is in procurement
     */
    public function isProcurement(): bool
    {
        return $this->hasRole('Procurement');
    }

    /**
     * Check if user is in sales
     */
    public function isSales(): bool
    {
        return $this->hasRole('Frontline Sales');
    }

    /**
     * Check if user can access a specific section
     */
    public function canAccessSection(?int $sectionId): bool
    {
        // Admin and Manager can access all sections
        if ($this->isAdmin() || $this->isManager()) {
            return true;
        }

        // Section-specific users can only access their section
        return $this->section_id === $sectionId;
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
