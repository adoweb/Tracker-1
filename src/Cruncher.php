<?php

namespace Kenarkose\Tracker;


use Carbon\Carbon;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class Cruncher {

    /** Config repository @var ConfigRepository */
    protected $config;

    /** Cache repository @var CacheRepository */
    protected $cache;

    /**
     * Constructor
     *
     * @param ConfigRepository $config
     * @param CacheRepository $cache
     */
    public function __construct(ConfigRepository $config, CacheRepository $cache)
    {
        $this->config = $config;
        $this->cache = $cache;
    }

    /**
     * Returns the last visited date
     *
     * @param string|null $locale
     * @param mixed $query
     * @return Carbon
     */
    public function getLastVisited($locale = null, $query = null)
    {
        $query = $this->determineLocaleAndQuery($locale, $query);

        $lastVisited = $query->orderBy('created_at', 'desc')->first();

        return $lastVisited ? $lastVisited->created_at : null;
    }

    /**
     * Gets the total visit count
     *
     * @param string|null $locale
     * @param mixed $query
     * @return int
     */
    public function getTotalVisitCount($locale = null, $query = null)
    {
        $query = $this->determineLocaleAndQuery($locale, $query);

        return $query->count();
    }

    /**
     * Gets today's visit count
     *
     * @param string|null $locale
     * @param mixed $query
     * @return int
     */
    public function getTodayCount($locale = null, $query = null)
    {
        $query = $this->determineLocaleAndQuery($locale, $query);

        return $query->where('created_at', '>=', Carbon::today())->count();
    }

    /**
     * Gets visit count in between dates
     *
     * @param Carbon $from
     * @param Carbon|null $until
     * @param string|null $locale
     * @param mixed $query
     * @return int
     */
    public function getCountInBetween(Carbon $from, Carbon $until = null, $locale = null, $query = null)
    {
        if (is_null($until))
        {
            $until = Carbon::now();
        }

        if ($count = $this->getCachedCountBetween($from, $until, $locale))
        {
            return $count;
        }

        $query = $this->determineLocaleAndQuery($locale, $query);

        $count = $query->whereBetween('created_at', [$from, $until])->count();

        $this->cacheCountBetween($count, $from, $until, $locale);

        return $count;
    }

    /**
     * Gets the cached count if there is any
     *
     * @param Carbon $from
     * @param Carbon $until
     * @param string|null $locale
     * @return int|null
     */
    protected function getCachedCountBetween(Carbon $from, Carbon $until, $locale = null)
    {
        $key = $this->makeBetweenCacheKey($from, $until, $locale);

        return $this->cache->get($key);
    }

    /**
     * Caches count between
     *
     * @param int $count
     * @param Carbon $from
     * @param Carbon $until
     * @param string|null $locale
     */
    protected function cacheCountBetween($count, Carbon $from, Carbon $until, $locale = null)
    {
        $key = $this->makeBetweenCacheKey($from, $until, $locale);

        $this->cache->put($key, $count);
    }

    /**
     * Makes the cache key
     *
     * @param Carbon $from
     * @param Carbon $until
     * @param string|null $locale
     * @return string
     */
    protected function makeBetweenCacheKey(Carbon $from, Carbon $until, $locale = null)
    {
        return 'tracker.between.'
            . (is_null($locale) ? '' : $locale . '.')
            . $from->timestamp . '-' . $until->timestamp;
    }

    /**
     * Get relative year visit count
     *
     * @param Carbon|null $end
     * @param string|null $locale
     * @param mixed $query
     * @return int
     */
    public function getRelativeYearCount($end = null, $locale = null, $query = null)
    {
        if (is_null($end))
        {
            $end = Carbon::today();
        }

        return $this->getCountInBetween($end->copy()->subYear()->startOfDay(), $end->endOfDay(), $locale, $query);
    }

    /**
     * Get relative month visit count
     *
     * @param Carbon|null $end
     * @param string|null $locale
     * @param mixed $query
     * @return int
     */
    public function getRelativeMonthCount($end = null, $locale = null, $query = null)
    {
        if (is_null($end))
        {
            $end = Carbon::today();
        }

        return $this->getCountInBetween($end->copy()->subMonth()->addDay()->startOfDay(), $end->endOfDay(), $locale, $query);
    }

    /**
     * Get relative week visit count
     *
     * @param Carbon|null $end
     * @param string|null $locale
     * @param mixed $query
     * @return int
     */
    public function getRelativeWeekCount($end = null, $locale = null, $query = null)
    {
        if (is_null($end))
        {
            $end = Carbon::today();
        }

        return $this->getCountInBetween($end->copy()->subWeek()->addDay()->startOfDay(), $end->endOfDay(), $locale, $query);
    }

    /**
     * Get relative day visit count
     *
     * @param Carbon|null $end
     * @param string|null $locale
     * @param mixed $query
     * @return int
     */
    public function getRelativeDayCount($end = null, $locale = null, $query = null)
    {
        if (is_null($end))
        {
            $end = Carbon::today();
        }

        return $this->getCountInBetween($end, $end->copy()->endOfDay(), $locale, $query);
    }

    /**
     * Gets count for given day
     *
     * @param Carbon|null $day
     * @param string|null $locale
     * @param mixed $query
     * @return int
     */
    public function getCountForDay(Carbon $day = null, $locale = null, $query = null)
    {
        if (is_null($day))
        {
            $day = Carbon::now();
        }

        return $this->getCountInBetween($day->startOfDay(), $day->copy()->endOfDay(), $locale, $query);
    }

    /**
     * Get count per month in between
     *
     * @param Carbon $from
     * @param Carbon|null $until
     * @param string|null $locale
     * @param mixed $query
     * @return array
     */
    public function getCountPerMonth(Carbon $from, Carbon $until = null, $locale = null, $query = null)
    {
        return $this->getCountPer('Month', $from, $until, $locale, $query);
    }

    /**
     * Get count per week in between
     *
     * @param Carbon $from
     * @param Carbon|null $until
     * @param string|null $locale
     * @param mixed $query
     * @return array
     */
    public function getCountPerWeek(Carbon $from, Carbon $until = null, $locale = null, $query = null)
    {
        return $this->getCountPer('Week', $from, $until, $locale, $query);
    }

    /**
     * Get count per day in between
     *
     * @param Carbon $from
     * @param Carbon|null $until
     * @param string|null $locale
     * @param mixed $query
     * @return array
     */
    public function getCountPerDay(Carbon $from, Carbon $until = null, $locale = null, $query = null)
    {
        return $this->getCountPer('Day', $from, $until, $locale, $query);
    }

    /**
     * Gets count per timespan
     *
     * @param string $span
     * @param Carbon $from
     * @param Carbon|null $until
     * @param string|null $locale
     * @param mixed $query
     * @return array
     */
    protected function getCountPer($span, Carbon $from, Carbon $until = null, $locale = null, $query = null)
    {
        $query = $this->determineLocaleAndQuery($locale, $query);

        $statistics = [];
        $labels = [];

        if (is_null($until))
        {
            $until = Carbon::now();
        }

        $until->startOfDay();

        do
        {
            $labels[] = $until->copy();
            $statistics[] = $this->{'getRelative' . $span . 'Count'}($until->copy(), null, clone $query);

            $until->{'sub' . $span}();
        } while ($until->gt($from));

        return [
            array_reverse($statistics),
            array_reverse($labels)
        ];
    }

    /**
     * Determines the query and locale
     *
     * @param $locale
     * @param $query
     * @return mixed
     */
    protected function determineLocaleAndQuery($locale, $query)
    {
        if (is_null($query))
        {
            $modelName = $this->getViewModelName();

            $query = with(new $modelName)->newQuery();
        }

        if ($locale)
        {
            $query->where('locale', $locale);
        }

        return $query;
    }

    /**
     * Returns the model name
     *
     * @return string
     */
    protected function getViewModelName()
    {
        return $this->config->get(
            'tracker.model',
            'Kenarkose\Tracker\SiteView'
        );
    }

}