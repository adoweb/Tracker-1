<?php

namespace Kenarkose\Tracker;


use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Session\Store;

class Tracker {

    /**
     * The current view instance
     *
     * @var SiteView
     */
    protected $current;

    /**
     * The current view hash
     *
     * @var string
     */
    protected $currentHash;

    /** Config repository @var Repository */
    protected $config;

    /** Current request @var Request */
    protected $request;

    /** Session store @var Store */
    protected $session;

    /** Laravel application @var Application */
    protected $app;

    /** Trackables @var array */
    protected $trackables = [];

    /**
     * Constructor
     *
     * @param Repository $config
     * @param Request $request
     * @param Store $session
     * @param Application $app
     */
    public function __construct(Repository $config, Request $request, Store $session, Application $app)
    {
        $this->config = $config;
        $this->request = $request;
        $this->session = $session;
        $this->app = $app;
    }

    /**
     * Creates a new view model
     *
     * @return SiteView
     */
    public function makeNewViewModel()
    {
        $modelName = $this->getViewModelName();

        return with(new $modelName)->fill(
            $this->collectVisitData()
        );
    }

    /**
     * Returns the model name
     *
     * @return string
     */
    public function getViewModelName()
    {
        return $this->config->get(
            'tracker.model',
            'Kenarkose\Tracker\SiteView'
        );
    }

    /**
     * Returns the current site view instance
     * creates if it is not created already
     *
     * @return SiteView
     */
    public function getCurrent()
    {
        if (is_null($this->current))
        {
            $siteView = $this->makeNewViewModel();

            $this->current = $siteView;
        }

        return $this->current;
    }

    /**
     * Collects the data for the site view model
     *
     * @return array
     */
    protected function collectVisitData()
    {
        $request = $this->request;

        $user = $request->user();
        $userId = $user ? $user->getKey() : null;

        return [
            'user_id'              => $userId,
            'http_referer'         => $request->server('HTTP_REFERER'),
            'url'                  => $request->fullUrl(),
            'request_method'       => $request->method(),
            'request_path'         => $request->getPathInfo(),
            'http_user_agent'      => $request->server('HTTP_USER_AGENT'),
            'http_accept_language' => $request->server('HTTP_ACCEPT_LANGUAGE'),
            'locale'               => $this->app->getLocale(),
            'request_time'         => $request->server('REQUEST_TIME')
        ];
    }

    /**
     * Persists the current site view to database
     *
     * @return bool
     */
    public function saveCurrent()
    {
        if ($this->saveEnabled() && $this->isViewUnique())
        {
            $success = $this->saveCurrentModel();

            // Keep on only if the model save has succeeded
            if ($success)
            {
                $this->storeCurrentHash();

                $this->saveTrackables(
                    $this->getCurrent(),
                    $success
                );
            }

            return $success;
        }

        return false;
    }

    /**
     * Checks if save is enabled
     *
     * @return mixed
     */
    protected function saveEnabled()
    {
        return $this->config->get('tracker.enabled', true);
    }

    /**
     * Checks if the current request is unique
     *
     * @return bool
     */
    public function isViewUnique()
    {
        $hash = $this->getCurrentHash();

        if (in_array($hash, $this->session->get('tracker.views', [])))
        {
            return false;
        }

        return true;
    }

    /**
     * Gets the view hash
     *
     * @return string
     */
    protected function getCurrentHash()
    {
        if ($this->currentHash === null)
        {
            $this->currentHash = md5(
                $this->request->fullUrl() .
                $this->request->method() .
                $this->request->getClientIp()
            );
        }

        return $this->currentHash;
    }

    /**
     * Saves the current model
     *
     * @return bool
     */
    protected function saveCurrentModel()
    {
        return $this->getCurrent()
            ->setAttribute('app_time', $this->getCurrentRuntime())
            ->setAttribute('memory', memory_get_peak_usage(true))
            ->save();
    }

    /**
     * Returns the current app runtime
     *
     * @return int
     */
    protected function getCurrentRuntime()
    {
        return round((microtime(true) - $this->request->server('REQUEST_TIME', microtime(true))) * 1000);
    }

    /**
     * Stores the current hash in session
     */
    protected function storeCurrentHash()
    {
        $this->session->push('tracker.views', $this->getCurrentHash());
    }

    /**
     * Saves the trackables
     *
     * @param $view
     * @param bool $success
     * @return bool
     */
    protected function saveTrackables($view, $success)
    {
        foreach ($this->trackables as $trackable)
        {
            $trackable->attachTrackerView($view);
        }

        return $success;
    }

    /**
     * Adds a trackable to the trackable stack
     *
     * @param TrackableInterface $trackable
     */
    public function addTrackable(TrackableInterface $trackable)
    {
        $this->trackables[] = $trackable;
    }

    /**
     * Flushes all SiteViews
     *
     * @return bool
     */
    public function flushAll()
    {
        return $this->flushOlderThanOrBetween();
    }

    /**
     * Flush older SiteViews
     *
     * @param timestamp $until
     * @return bool
     */
    public function flushOlderThan($until)
    {
        return $this->flushOlderThanOrBetween($until);
    }

    /**
     * Flush older than or between SiteViews
     *
     * @param timestamp $until
     * @param timestamp $from
     * @return bool
     */
    public function flushOlderThanOrBetween($until = null, $from = null)
    {
        $modelName = $this->getViewModelName();

        return $modelName::olderThanOrBetween($until, $from)->delete();
    }
}