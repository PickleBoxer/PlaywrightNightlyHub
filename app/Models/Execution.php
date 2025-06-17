<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Execution extends Model
{
    use HasFactory;

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

    public function suites(): HasMany
    {
        return $this->hasMany(Suite::class);
    }

    /**
     * Scope a query to filter by platform.
     */
    #[Scope]
    protected function platform(Builder $query, ?string $platform): void
    {
        if ($platform) {
            $query->where('platform', $platform);
        }
    }

    /**
     * Scope a query to filter by campaign.
     */
    #[Scope]
    protected function campaign(Builder $query, ?string $campaign): void
    {
        if ($campaign) {
            $query->where('campaign', $campaign);
        }
    }

    /**
     * Scope a query to filter by version.
     */
    #[Scope]
    protected function version(Builder $query, ?string $version): void
    {
        if ($version) {
            $query->where('version', $version);
        }
    }

    /**
     * Scope a query to search version, campaign, platform, or filename.
     */
    #[Scope]
    protected function search(Builder $query, ?string $search): void
    {
        if ($search) {
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
     */
    #[Scope]
    protected function recent(Builder $query, ?int $days = 7): void
    {
        $query->where('start_date', '>=', Carbon::now()->subDays($days));
    }
}
