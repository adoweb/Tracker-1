<?php

class TrackerServiceProviderTest extends TestBase {

    /** @test */
    function it_registers_the_tracker_service()
    {
        $this->assertInstanceOf(
            'Kenarkose\Tracker\Tracker',
            $this->app['tracker']
        );
    }

    /** @test */
    function it_allows_access_via_helper()
    {
        $this->assertInstanceOf(
            'Kenarkose\Tracker\Tracker',
            app('tracker')
        );
    }

}