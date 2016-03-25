<?php

use Carbon\Carbon;
use Kenarkose\Tracker\SiteView;

class CruncherTest extends TestBase {

    public function setUp()
    {
        parent::setUp();

        SiteView::truncate();

        session()->flush();
    }

    /**
     * Gets a new cruncher instance
     */
    protected function getCruncher()
    {
        return $this->app->make('Kenarkose\Tracker\Cruncher');
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
    function it_gets_last_visited_date()
    {
        $cruncher = $this->getCruncher();
        $tracker = $this->getTracker();

        $this->assertNull(
            $cruncher->getLastVisited()
        );

        $tracker->saveCurrent();

        $this->assertInstanceOf(
            'Carbon\Carbon',
            $cruncher->getLastVisited()
        );

        $this->assertInstanceOf(
            'Carbon\Carbon',
            $cruncher->getLastVisited('en')
        );

        $this->assertNull(
            $cruncher->getLastVisited('tr')
        );
    }

    /** @test */
    function it_gets_last_visited_count()
    {
        $cruncher = $this->getCruncher();
        $tracker = $this->getTracker();

        $this->assertEquals(
            0,
            $cruncher->getTotalVisitCount()
        );

        $tracker->saveCurrent();

        $this->assertEquals(
            1,
            $cruncher->getTotalVisitCount()
        );

        $this->assertEquals(
            1,
            $cruncher->getTotalVisitCount('en')
        );

        $this->assertEquals(
            0,
            $cruncher->getTotalVisitCount('tr')
        );
    }

    /** @test */
    function it_gets_today_count()
    {
        $cruncher = $this->getCruncher();
        $tracker = $this->getTracker();

        $this->assertEquals(
            0,
            $cruncher->getTodayCount()
        );

        $tracker->saveCurrent();

        $this->assertEquals(
            1,
            $cruncher->getTodayCount()
        );

        $this->assertEquals(
            1,
            $cruncher->getTodayCount('en')
        );

        $this->assertEquals(
            0,
            $cruncher->getTodayCount('tr')
        );
    }

    /** @test */
    function it_gets_count_it_between()
    {
        $cruncher = $this->getCruncher();
        $tracker = $this->getTracker();

        $this->assertEquals(
            0,
            $cruncher->getCountInBetween(Carbon::yesterday(), Carbon::now())
        );

        $tracker->saveCurrent();

        $this->assertEquals(
            0,
            $cruncher->getCountInBetween(Carbon::yesterday()->subDay(), Carbon::yesterday())
        );

        $this->assertEquals(
            1,
            $cruncher->getCountInBetween(Carbon::yesterday(), Carbon::now())
        );

        $this->assertEquals(
            1,
            $cruncher->getCountInBetween(Carbon::yesterday(), Carbon::now(), 'en')
        );

        $this->assertEquals(
            0,
            $cruncher->getCountInBetween(Carbon::yesterday(), Carbon::now(), 'tr')
        );
    }

    /** @test */
    function it_gets_relative_year_count()
    {
        $cruncher = $this->getCruncher();
        $tracker = $this->getTracker();

        $this->assertEquals(
            0,
            $cruncher->getRelativeYearCount()
        );

        $tracker->saveCurrent();

        $this->assertEquals(
            1,
            $cruncher->getRelativeYearCount()
        );

        $this->assertEquals(
            1,
            $cruncher->getRelativeYearCount(null, 'en')
        );

        $this->assertEquals(
            0,
            $cruncher->getRelativeYearCount(null, 'tr')
        );
    }

    /** @test */
    function it_gets_relative_month_count()
    {
        $cruncher = $this->getCruncher();
        $tracker = $this->getTracker();

        $this->assertEquals(
            0,
            $cruncher->getRelativeMonthCount()
        );

        $tracker->saveCurrent();

        $this->assertEquals(
            1,
            $cruncher->getRelativeMonthCount()
        );

        $this->assertEquals(
            1,
            $cruncher->getRelativeMonthCount(null, 'en')
        );

        $this->assertEquals(
            0,
            $cruncher->getRelativeMonthCount(null, 'tr')
        );
    }

    /** @test */
    function it_gets_relative_week_count()
    {
        $cruncher = $this->getCruncher();
        $tracker = $this->getTracker();

        $this->assertEquals(
            0,
            $cruncher->getRelativeWeekCount()
        );

        $tracker->saveCurrent();

        $this->assertEquals(
            1,
            $cruncher->getRelativeWeekCount()
        );

        $this->assertEquals(
            1,
            $cruncher->getRelativeWeekCount(null, 'en')
        );

        $this->assertEquals(
            0,
            $cruncher->getRelativeWeekCount(null, 'tr')
        );
    }

    /** @test */
    function it_gets_count_for_day()
    {
        $cruncher = $this->getCruncher();
        $tracker = $this->getTracker();

        $this->assertEquals(
            0,
            $cruncher->getCountForDay()
        );

        $tracker->saveCurrent();

        $this->assertEquals(
            1,
            $cruncher->getCountForDay()
        );

        $this->assertEquals(
            0,
            $cruncher->getCountForDay(Carbon::yesterday())
        );
    }

    /** @test */
    function it_gets_count_per_month()
    {
        $cruncher = $this->getCruncher();

        list($statistics, $labels) = $cruncher->getCountPerMonth(Carbon::today()->subYear());

        $this->assertCount(
            12,
            $statistics
        );
    }

    /** @test */
    function it_gets_count_per_week()
    {
        $cruncher = $this->getCruncher();

        list($statistics, $labels) = $cruncher->getCountPerWeek(Carbon::today()->subWeek(4));

        $this->assertCount(
            4,
            $statistics
        );
    }

    /** @test */
    function it_gets_count_per_day()
    {
        $cruncher = $this->getCruncher();

        list($statistics, $labels) = $cruncher->getCountPerDay(Carbon::today()->subWeek());

        $this->assertCount(
            7,
            $statistics
        );
    }

}