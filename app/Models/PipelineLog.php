<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PipelineLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'from_stage',
        'to_stage',
        'moved_by',
        'note',
        'ref_code',
    ];

    protected function casts(): array
    {
        return [
            'from_stage' => 'integer',
            'to_stage' => 'integer',
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function mover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moved_by');
    }
}
