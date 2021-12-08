<?php

namespace TaiwanSms\TwSMS\Tests;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use TaiwanSms\TwSMS\TwSMSMessage;

class TwSMSMessageTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testConstruct()
    {
        $message = new TwSMSMessage(
            $content = 'foo'
        );

        $this->assertSame($content, $message->content);
    }

    public function testContent()
    {
        $message = new TwSMSMessage();
        $message->content(
            $content = 'foo'
        );

        $this->assertSame($content, $message->content);
    }

    public function testCreate()
    {
        $message = TwSMSMessage::create(
            $content = 'foo'
        );

        $this->assertSame($content, $message->content);
    }
}
