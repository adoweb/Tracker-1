<?php

use Kenarkose\Tracker\Tracker;

class TrackerTest extends TestBase {

    /**
     * Setup test
     */
    public function setUp()
    {
        parent::setUp();

        $this->app['router']->get('/home', function ()
        {
            return '';
        });
        $this->app['router']->get('/test', function ()
        {
            return '';
        });

        $this->app['session']->set('tracker.views', []);
    }

    /**
     * Returns the instance
     *
     * @return Tracker
     */
    protected function getTracker()
    {
        return $this->app->make('Kenarkose\Tracker\Tracker');
    }

    /** @test */
    function it_returns_the_model_name()
    {
        $tracker = $this->getTracker();

        $this->assertEquals(
            'Kenarkose\Tracker\SiteView',
            $tracker->getViewModelName()
        );
    }

    /** @test */
    function it_makes_a_new_site_view_model()
    {
        $tracker = $this->getTracker();

        $this->assertInstanceOf(
            'Kenarkose\Tracker\SiteView',
            $tracker->makeNewViewModel()
        );
    }

    /** @test */
    function it_returns_the_current_view()
    {
        $tracker = $this->getTracker();

        $this->assertInstanceOf(
            'Kenarkose\Tracker\SiteView',
            $tracker->getCurrent()
        );
    }

    /** @test */
    function it_checks_if_the_current_view_is_unique()
    {
        $tracker = $this->getTracker();

        $this->visit('/home');

        $this->assertTrue(
            $tracker->isViewUnique()
        );

        $tracker->saveCurrent();

        $this->assertFalse(
            $tracker->isViewUnique()
        );
    }

    /** @test */
    function it_saves_the_current_view()
    {
        $tracker = $this->getTracker();

        $this->assertTrue(
            $tracker->saveCurrent()
        );
    }

    /** @test */
    function it_saves_only_if_unique()
    {
        $tracker = $this->getTracker();

        $this->assertTrue(
            $tracker->saveCurrent()
        );

        $this->assertFalse(
            $tracker->saveCurrent()
        );
    }

    /** @test */
    function it_adds_and_saves_trackables()
    {
        $tracker = $this->getTracker();

        $view = $tracker->getCurrent();

        $trackable = $this->prophesize('Kenarkose\Tracker\TrackableInterface');
        $trackable->attachTrackerView($view)
            ->willReturn(null)
            ->shouldBeCalled();

        $tracker->addTrackable($trackable->reveal());

        $tracker->saveCurrent();
    }

}