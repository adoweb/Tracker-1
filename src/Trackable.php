<?php

namespace Kenarkose\Tracker;


trait Trackable {

    /**
     * SiteView relation
     *
     * @return BelongsToMany
     */
    public function trackerViews()
    {
        return $this->belongsToMany(
            tracker()->getViewModelName());
    }

    /**
     * Attaches a tracker view
     *
     * @param $view
     */
    public function attachTrackerView($view)
    {
        if ( ! $this->trackerViews->contains($view->getKey()))
        {
            return $this->trackerViews()->attach($view);
        }
    }

}