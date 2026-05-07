<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'department',
        'location',
        'description',
        'requirements',
        'commission_amount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'commission_amount' => 'integer',
        ];
    }

    public function formFields(): HasMany
    {
        return $this->hasMany(JobFormField::class)->orderBy('order');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }
}
