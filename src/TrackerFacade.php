<?php

namespace Kenarkose\Tracker;


use Illuminate\Support\Facades\Facade;

class TrackerFacade extends Facade{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'tracker';
    }

}