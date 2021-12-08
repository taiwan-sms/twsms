<?php

namespace TaiwanSms\TwSMS\Tests;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use TaiwanSms\TwSMS\TwSMSChannel;
use TaiwanSms\TwSMS\TwSMSMessage;

class TwSMSChannelTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function checkValid()
    {
        if (PHP_VERSION_ID < 50600 === true) {
            $this->markTestSkipped('PHP VERSION must bigger then 5.6');
        }
    }

    public function testSend()
    {
        $this->checkValid();

        $channel = new TwSMSChannel(
            $client = m::mock('TaiwanSms\TwSMS\Client')
        );

        $client->expects('send')->with([
            'to' => $to = '+1234567890',
            'text' => $message = 'foo',
        ]);

        $notifiable = new TestNotifiable(function () use ($to) {
            return $to;
        });
        $notification = new TestNotification(function () use ($message) {
            return $message;
        });

        $channel->send($notifiable, $notification);
    }

    public function testSendMessage()
    {
        $this->checkValid();

        $channel = new TwSMSChannel(
            $client = m::mock('TaiwanSms\TwSMS\Client')
        );

        $client->expects('send')->with([
            'to' => $to = '+1234567890',
            'text' => $message = 'foo',
        ]);

        $notifiable = new TestNotifiable(function () use ($to) {
            return $to;
        });
        $notification = new TestNotification(function () use ($message) {
            return TwSMSMessage::create($message)->subject('subject');
        });

        $channel->send($notifiable, $notification);
    }

    public function testSendFail()
    {
        $this->checkValid();

        $channel = new TwSMSChannel(
            m::mock('TaiwanSms\TwSMS\Client')
        );

        $notifiable = new TestNotifiable(function () {
            return false;
        });
        $notification = new TestNotification(function () {
            return false;
        });

        self::assertNull($channel->send($notifiable, $notification));
    }
}

class TestNotifiable
{
    use Notifiable;

    protected $resolver;

    public function __construct($resolver)
    {
        $this->resolver = $resolver;
    }

    public function routeNotificationForTwSMS()
    {
        $resolver = $this->resolver;

        return $resolver();
    }
}

class TestNotification extends Notification
{
    protected $resolver;

    public function __construct($resolver)
    {
        $this->resolver = $resolver;
    }

    public function toTwSMS()
    {
        $resolver = $this->resolver;

        return $resolver();
    }
}
