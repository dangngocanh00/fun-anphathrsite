<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobFormField extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'label',
        'type',
        'options',
        'is_required',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(CandidateFormAnswer::class, 'field_id');
    }
}
