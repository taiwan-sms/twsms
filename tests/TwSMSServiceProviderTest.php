<?php

namespace TaiwanSms\TwSMS\Tests;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use TaiwanSms\TwSMS\Client;
use TaiwanSms\TwSMS\TwSMSServiceProvider;

class TwSMSServiceProviderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testRegister()
    {
        if (PHP_VERSION_ID < 50600 === true) {
            $this->markTestSkipped('PHP VERSION must bigger then 5.6');
        }

        $config = new Repository();
        $config->set('services.twsms', ['username' => 'foo', 'password' => 'bar']);
        $app = m::mock(new Container());
        $app->instance('config', $config);

        $serviceProvider = new TwSMSServiceProvider($app);

        $app->expects('singleton')->with('TaiwanSms\TwSMS\Client', m::on(function ($closure) use ($app) {
            return $closure($app) instanceof Client;
        }));

        $serviceProvider->register();
    }
}
