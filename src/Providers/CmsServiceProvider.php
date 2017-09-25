<?php

namespace Sherrycin\Cms\Providers;


use Sherrycin\Cms\Facades\Cms;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;


class CmsServiceProvider extends ServiceProvider
{
	
	/**
	 * @var array
	 */
	protected $commands = [
	];
	
	/**
	 * The application's route middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = [
	];
	
	/**
	 * The application's route middleware groups.
	 *
	 * @var array
	 */
	protected $middlewareGroups = [
	];
	
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    	$this->loadViewsFrom(__DIR__.'/../../views', 'cms');
    	$this->loadTranslationsFrom(__DIR__.'/../../lang/', 'cms');
    	if ($this->app->runningInConsole()) {
    		$this->publishes([__DIR__.'/../resources/lang' => resource_path('lang')], 'laravel-admin-lang');
    		$this->publishes([__DIR__.'/../../database/migrations' => database_path('migrations')], 'laravel-cms-migrations');
    	}
    	/**
    	$this->publishes([__DIR__.'/../../config/admin.php' => config_path('admin.php')], 'laravel-admin');
    	$this->publishes([__DIR__.'/../../assets' => public_path('packages/admin')], 'laravel-admin');
    	**/
    	//Cms::registerAdminRoutes();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    	if (is_null(config('auth.guards.admin'))) {
    		$this->setupAuth();
    	}
        /**
    	$this->app->booting(function () {
    		$loader = AliasLoader::getInstance();
    	
    		$loader->alias('Cms', \Sherry\Cms\Facades\Cms::class);
    		if (is_null(config('auth.guards.admin'))) {
    			$this->setupAuth();
    		}
    	});
    	**/
    }
    
    /**
     * Setup auth configuration.
     *
     * @return void
     */
    protected function setupAuth()
    {
    	config([
    			'auth.guards.admin.driver'    => 'session',
    			'auth.guards.admin.provider'  => 'admin',
    			'auth.providers.admin.driver' => 'eloquent',
    			'auth.providers.admin.model'  => 'Encore\Admin\Auth\Database\Administrator',
    	]);
    }
}
