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
            tracker()->getViewModelName(),
            $this->getTrackerPivotTableName(),
            $this->getTrackerForeignKey()
        );
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

    /**
     * Getter for table name
     *
     * @return string|null
     */
    protected function getTrackerPivotTableName()
    {
        if (property_exists($this, 'trackerPivotTable'))
        {
            return $this->trackerPivotTable;
        }
    }

    /**
     * Getter for foreign key
     *
     * @return string|null
     */
    protected function getTrackerForeignKey()
    {
        if (property_exists($this, 'trackerForeignKey'))
        {
            return $this->trackerForeignKey;
        }
    }

}