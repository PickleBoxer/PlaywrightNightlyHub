<?php

declare(strict_types=1);

namespace App\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Suite extends Model
{
    //use HasFactory;

    protected $fillable = [
        'execution_id',
        'uuid',
        'title',
        'has_skipped',
        'has_pending',
        'has_passes',
        'has_failures',
        'has_suites',
        'has_tests',
        'total_skipped',
        'total_pending',
        'total_passes',
        'total_failures',
        'parent_id',
        'campaign',
        'file',
        'duration',
        'insertion_date',
    ];

    protected $casts = [
        'has_skipped' => 'boolean',
        'has_pending' => 'boolean',
        'has_passes' => 'boolean',
        'has_failures' => 'boolean',
        'has_suites' => 'boolean',
        'has_tests' => 'boolean',
        'insertion_date' => 'datetime',
    ];

    /**
     * @return BelongsTo<Execution, $this>
     */
    public function execution(): BelongsTo
    {
        return $this->belongsTo(Execution::class);
    }

    /**
     * @return HasMany<Test, $this>
     */
    public function tests(): HasMany
    {
        return $this->hasMany(Test::class);
    }

    /**
     * @return HasMany<Suite, $this>
     */
    public function childSuites(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return BelongsTo<Suite, $this>
     */
    public function parentSuite(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
}
