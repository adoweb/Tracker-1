<?php

namespace Kenarkose\Tracker;


use Closure;

class TrackerMiddleware {

    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        tracker()->saveCurrent();

        return $response;
    }

}