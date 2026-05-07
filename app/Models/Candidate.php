<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Candidate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'cv_link',
        'job_id',
        'assigned_hr_id',
        'current_stage',
        'ai_score',
        'ai_flags',
        'ai_questions',
        'ai_analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'current_stage' => 'integer',
            'ai_score' => 'integer',
            'ai_flags' => 'array',
            'ai_questions' => 'array',
            'ai_analyzed_at' => 'datetime',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function assignedHr(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_hr_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(CandidateFormAnswer::class);
    }

    public function pipelineLogs(): HasMany
    {
        return $this->hasMany(PipelineLog::class)->orderBy('created_at');
    }

    public function interviewNotes(): HasMany
    {
        return $this->hasMany(InterviewNote::class)->latest();
    }
}
