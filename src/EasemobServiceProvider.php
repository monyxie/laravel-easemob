<?php

namespace link1st\Easemob;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use link1st\Easemob\App\Easemob;

class EasemobServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;


    /**
     * 引导程序
     *
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // 发布配置文件 + 可以发布迁移文件
        $this->publishes([
            __DIR__ . '/config/easemob.php' => config_path('easemob.php'),
        ]);
    }


    /**
     * 默认包位置
     *
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // 将给定配置文件合现配置文件接合
        $this->mergeConfigFrom(
            __DIR__ . '/config/easemob.php', 'easemob'
        );

        // 容器绑定
        $this->app->singleton(Easemob::class, function () {
            $config['domain_name'] = Config::get('easemob.domain_name');
            $config['org_name'] = Config::get('easemob.org_name');
            $config['app_name'] = Config::get('easemob.app_name');
            $config['client_id'] = Config::get('easemob.client_id');
            $config['client_secret'] = Config::get('easemob.client_secret');
            $config['token_cache_time'] = Config::get('easemob.token_cache_time');

            return new Easemob($config, $this->app->make('cache'));
        });

        $this->app->alias(Easemob::class, 'Easemob');
    }


    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
