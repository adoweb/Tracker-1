<?php

if ( ! function_exists('tracker'))
{

    /**
     * Shorthand for the tracker instance
     *
     * @return Kenarkose\Tracker\Tracker
     */
    function tracker()
    {
        return app()->make('tracker');
    }

}