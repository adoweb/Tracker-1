<?php

namespace Kenarkose\Tracker;


interface TrackableInterface {

    /**
     * Attaches the view to trackable
     *
     * @param $view
     */
    public function attachTrackerView($view);

}