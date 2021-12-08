<?php

namespace TaiwanSms\TwSMS\Tests;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use TaiwanSms\TwSMS\Client;

class ClientRealTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected $username = '';
    protected $password = '';
    protected $options = [
        'to' => '',
        'text' => '中文測試',
        'msgid' => '',
    ];

    private function checkValid()
    {
        if (empty($this->username) === true || empty($this->password) === true) {
            $this->markTestSkipped('Please set uid and password');
        }
    }

    public function testQuery()
    {
        $this->checkValid();

        $client = new Client($this->username, $this->password);

        self::assertIsArray($client->query([
            'mobile' => $this->options['to'],
            'msgid' => $this->options['msgid'],
        ]));
    }

    public function testSend()
    {
        $this->checkValid();

        $client = new Client($this->username, $this->password);

        self::assertIsArray($client->send([
            'to' => $this->options['to'],
            'text' => $this->options['text'],
        ]));
    }
}
