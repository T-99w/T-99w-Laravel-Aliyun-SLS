<?php

namespace T99w\Aliyunlogsls;

use Illuminate\Support\ServiceProvider;
use function T99w\Aliyunlogsls\ServiceProvider\config_path;

class AliyunSlsServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/src/config/aliyunlog.php' => config_path('aliyunlog.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/src/config/aliyunlog.php', 'aliyunlog'
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }

}
