<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Execution extends Model
{
    // use HasFactory;

    protected $fillable = [
        'ref',
        'filename',
        'version',
        'campaign',
        'platform',
        'database',
        'start_date',
        'end_date',
        'duration',
        'suites',
        'tests',
        'skipped',
        'pending',
        'passes',
        'failures',
        'broken_since_last',
        'fixed_since_last',
        'equal_since_last',
        'insertion_start_date',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'insertion_start_date' => 'datetime',
    ];

    /**
     * @return HasMany<Suite, $this>
     */
    public function suites(): HasMany
    {
        return $this->hasMany(Suite::class);
    }

    /**
     * Scope a query to filter by platform.
     *
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function platform(Builder $query, ?string $platform): void
    {
        if ($platform !== null && $platform !== '' && $platform !== '0') {
            $query->where('platform', $platform);
        }
    }

    /**
     * Scope a query to filter by campaign.
     *
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function campaign(Builder $query, ?string $campaign): void
    {
        if ($campaign !== null && $campaign !== '' && $campaign !== '0') {
            $query->where('campaign', $campaign);
        }
    }

    /**
     * Scope a query to filter by version.
     *
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function version(Builder $query, ?string $version): void
    {
        if ($version !== null && $version !== '' && $version !== '0') {
            $query->where('version', $version);
        }
    }

    /**
     * Scope a query to search version, campaign, platform, or filename.
     *
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function search(Builder $query, ?string $search): void
    {
        if ($search !== null && $search !== '' && $search !== '0') {
            $query->where(function ($q) use ($search): void {
                $q->where('version', 'like', "%{$search}%")
                    ->orWhere('campaign', 'like', "%{$search}%")
                    ->orWhere('platform', 'like', "%{$search}%")
                    ->orWhere('filename', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Scope a query to recent reports (within the last n days).
     *
     * @param  Builder<$this>  $query
     */
    #[Scope]
    protected function recent(Builder $query, ?int $days = 7): void
    {
        $query->where('start_date', '>=', Carbon::now()->subDays($days));
    }
}
