# Tracker
Simple site visit/statistics tracker for Laravel 5.

---
[![Build Status](https://travis-ci.org/kenarkose/Tracker.svg?branch=master)](https://travis-ci.org/kenarkose/Tracker)
[![Total Downloads](https://poser.pugx.org/kenarkose/Tracker/downloads)](https://packagist.org/packages/kenarkose/Tracker)
[![Latest Stable Version](https://poser.pugx.org/kenarkose/Tracker/version)](https://packagist.org/packages/kenarkose/Tracker)
[![License](https://poser.pugx.org/kenarkose/Tracker/license)](https://packagist.org/packages/kenarkose/Tracker)

Tracker provides a simple way to track your site visits and their statistics.

## Features
- Compatible with Laravel 5
- Middleware for automatically recording the site view
- Associate site views to Eloquent models to track their views
- Persists unique views based on URL, method, and IP address
- Helper method, Facade, and trait for easing access to services
- Handy 'Cruncher' for number crunching needs
- Flushing and selecting site views with given time spans
- A [phpunit](http://www.phpunit.de) test suite for easy development

## Installation
Installing Tracker is simple.

1. Pull this package in through [Composer](https://getcomposer.org).

    ```js
    {
        "require": {
            "kenarkose/tracker": "~1.0"
        }
    }
    ```

2. In order to register Tracker Service Provider add `'Kenarkose\Tracker\TrackerServiceProvider'` to the end of `providers` array in your `config/app.php` file.
    ```php
    'providers' => array(
    
        'Illuminate\Foundation\Providers\ArtisanServiceProvider',
        'Illuminate\Auth\AuthServiceProvider',
        ...
        'Kenarkose\Tracker\TrackerServiceProvider',
    
    ),
    ```

3. You may configure the default behaviour of Tracker by publishing and modifying the configuration file. To do so, use the following command.
    ```bash
    php artisan vendor:publish
    ```
    Than, you will find the configuration file on the `config/tracker.php` path. Information about the options can be found in the comments of this file. All of the options in the config file are optional, and falls back to default if not specified; remove an option if you would like to use the default.
    
    This will also publish the migration file for the default `SiteView` model. Do not forget to migrate your database before using Tracker.

4. In order to register the Facade add the following line to the end of `aliases` array in your `config/app.php` file.
   ```php
   'aliases' => array(
   
       'App'        => 'Illuminate\Support\Facades\App',
       'Artisan'    => 'Illuminate\Support\Facades\Artisan',
       ...
       'Tracker'   => 'Kenarkose\Tracker\TrackerFacade'
   
   ),
   ```

5. You may now access Tracker either by the Facade or the helper function.
    ```php
    tracker()->getCurrent();
    Tracker::saveCurrent();
    
    tracker()->isViewUnique();
    tracker()->isViewValid();
    
    tracker()->addTrackable($post);
    
    Tracker::flushAll();
    Tracker::flushOlderThan(Carbon::now());
    Tracker::flushOlderThenOrBetween(Carbon::now(), Carbon::now()->subYear());
    ```

6. It is important to record views by using the supplied middleware to record correct app runtime and memory information. To do so register the middleware in `app\Http\Kernel`.
    ```php
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'guard' => \App\Http\Middleware\Guard::class,
        'track' => \Kenarkose\Tracker\TrackerMiddleware::class
    ];
    ```
    It is better to register this middleware as a routeMiddleware instead of a global middleware and use it in routes or route groups definitions as it may not be necessary to persist all site view. This will persist and attach any Trackable that is added to stack to site views automatically when the request has been handled by Laravel.
    
7. To attach views to any model or class, you should implement the `Kenarkose\Tracker\TrackableInterface` interface. Tracker provides `Kenarkose\Tracker\Trackable` trait to be used by Eloquent models.
    ```php
        
        use Illuminate\Database\Eloquent\Model as Eloquent;
        use Kenarkose\Tracker\Trackable;
        use Kenarkose\Tracker\TrackableInterface;
        
        class Node extends Eloquent implements TrackableInterface {
            
            use Trackable;
            
        }
    ```
    
    The `Trackable` trait uses Eloquent's `belongsToMany` relationship which utilizes pivot tables. Here is a sample migration for the pivot table:
    ```php
        <?php
        
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Database\Migrations\Migration;
        
        class CreateNodeSiteViewPivotTable extends Migration {
        
            /**
             * Run the migrations.
             *
             * @return void
             */
            public function up()
            {
                Schema::create('node_site_view', function (Blueprint $table)
                {
                    $table->integer('node_id')->unsigned();
                    $table->integer('site_view_id')->unsigned();
        
                    $table->foreign('node_id')
                        ->references('id')
                        ->on('nodes')
                        ->onDelete('cascade');
        
                    $table->foreign('site_view_id')
                        ->references('id')
                        ->on('site_views')
                        ->onDelete('cascade');
        
                    $table->primary(['node_id', 'site_view_id']);
                });
            }
        
            /**
             * Reverse the migrations.
             *
             * @return void
             */
            public function down()
            {
                Schema::drop('node_site_view');
            }
        }

    ```
    
8. Check the `Kenarkose\Tracker\Cruncher` class and test for statistics number crunching. It is equipped with a number of methods for different types of statistics (mostly counts) in different time spans.

Please check the tests and source code for further documentation, as the source code of Tracker is well tested and documented.

## License
Tracker is released under [MIT License](https://github.com/kenarkose/Tracker/blob/master/LICENSE).
