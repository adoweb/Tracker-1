<?php

namespace Kenarkose\Tracker;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;

class SiteView extends Eloquent {

    /**
     * Disable timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Fillable attributes
     */
    public $fillable = [
        'user_id', 'http_referer', 'url',
        'request_method', 'request_path',
        'http_user_agent', 'http_accept_language', 'locale',
        'request_time', 'app_time', 'memory'
    ];

    /**
     * Boot the model
     */
    public static function boot()
    {
        static::creating(function ($model)
        {
            $model->created_at = $model->freshTimestamp();
        });
    }

    /**
     * Scope for choosing by date
     *
     * @param Builder $query
     * @param timestamp $until
     * @param timestamp|null $from
     * @return Builder
     */
    public function scopeOlderThenOrBetween(Builder $query, $until = null, $from = null)
    {
        if (is_null($until))
        {
            $until = Carbon::now();
        }

        $query->where('created_at', '<', $until);

        if ( ! is_null($from))
        {
            $query->where('created_at', '>=', $from);
        }

        return $query;
    }


}