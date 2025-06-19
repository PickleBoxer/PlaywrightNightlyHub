<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Test extends Model
{
    // use HasFactory;

    protected $fillable = [
        'suite_id',
        'uuid',
        'title',
        'state',
        'identifier',
        'duration',
        'error_message',
        'stack_trace',
        'diff',
        'insertion_date',
    ];

    protected $casts = [
        'insertion_date' => 'datetime',
    ];

    /**
     * Summary of suite
     *
     * @return BelongsTo<Suite, $this>
     */
    public function suite(): BelongsTo
    {
        return $this->belongsTo(Suite::class);
    }
}
