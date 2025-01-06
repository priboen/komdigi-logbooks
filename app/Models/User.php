<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'roles',
        'photo'
    ];


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

    public function internshipsAsLeader()
    {
        return $this->hasMany(Internship::class, 'leader_id');
    }

    public function internshipsAsMember()
    {
        return $this->belongsToMany(Internship::class, 'internship_members', 'user_id', 'internship_id');
    }

    public function internshipsAsSupervisor()
    {
        return $this->hasMany(Internship::class, 'supervisor_id');
    }

    public function scopeWithRole($query, $role)
    {
        return $query->where('roles', $role);
    }
}
