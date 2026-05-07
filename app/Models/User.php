<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function assignedCandidates(): HasMany
    {
        return $this->hasMany(Candidate::class, 'assigned_hr_id');
    }

    public function pipelineLogs(): HasMany
    {
        return $this->hasMany(PipelineLog::class, 'moved_by');
    }

    public function interviewNotes(): HasMany
    {
        return $this->hasMany(InterviewNote::class);
    }
}
