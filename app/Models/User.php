<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Society;

class User extends Authenticatable
{
    use HasFactory, Notifiable,SoftDeletes;

    protected $fillable = [
        'name',
        'society_id',
        'email',
        'phone',
        'password',
        'role_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function resident()
    {
        return $this->hasOne(Resident::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function visitorLogs()
    {
        return $this->hasMany(VisitorLog::class, 'gatekeeper_id');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function society()
    {
        return $this->belongsTo(Society::class);
    }    

    public function isSuperAdmin()
    {
        return $this->role?->name === 'super_admin';
    }

    public function isAdmin()
    {
        return $this->role?->name === 'admin';
    }

    public function isResident()
    {
        return $this->role?->name === 'resident';
    }

    public function isGatekeeper()
    {
        return $this->role?->name === 'gatekeeper';
    }
}
