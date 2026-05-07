<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateFormAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'field_id',
        'answer',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(JobFormField::class, 'field_id');
    }
}
