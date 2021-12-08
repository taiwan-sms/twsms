<?php

namespace TaiwanSms\TwSMS;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class TwSMSServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Client::class, function ($app) {
            $config = Arr::get($app['config'], 'services.twsms', []);

            return new Client(Arr::get($config, 'username'), Arr::get($config, 'password'));
        });
    }
}
