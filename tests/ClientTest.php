<?php

namespace TaiwanSms\TwSMS\Tests;

use Carbon\Carbon;
use DomainException;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use TaiwanSms\TwSMS\Client;

class ClientTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testQuery()
    {
        $client = new Client(
            $username = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $params = [
            'mobile' => 'foo',
            'msgid' => '265078525',
        ];

        $query = array_filter(array_merge([
            'username' => $username,
            'password' => $password,
        ], [
            'mobile' => $params['mobile'],
            'msgid' => $params['msgid'],
        ]));

        $messageFactory->expects('createRequest')->with(
            'POST',
            'http://api.twsms.com/smsQuery.php',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($query)
        )->andReturns(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->expects('sendRequest')->with($request)->andReturns(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->expects('getBody->getContents')->andReturns(
            '<smsResp>
                <code>00000</code>
                <text>Success</text>
                <statuscode></statuscode>
                <statustext></statustext>
                <donetime></donetime>
            </smsResp>'
        );

        $this->assertSame([
            'code' => '00000',
            'text' => 'Success',
        ], $client->query($params));
    }

    public function testCredit()
    {
        $client = new Client(
            $username = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $params = [
            'checkpoint' => 'Y',
        ];

        $query = array_filter(array_merge([
            'username' => $username,
            'password' => $password,
        ], [
            'checkpoint' => $params['checkpoint'],
        ]));

        $messageFactory->expects('createRequest')->with(
            'POST',
            'http://api.twsms.com/smsQuery.php',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($query)
        )->andReturns(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->expects('sendRequest')->with($request)->andReturns(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->expects('getBody->getContents')->andReturns(
            '<smsResp>
                <code>00000</code>
                <text>Success</text>
                <point>6</point>
            </smsResp>'
        );

        $this->assertSame(6, $client->credit());
    }

    public function testSend()
    {
        $client = new Client(
            $username = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $params = [
            'to' => 'foo',
            'text' => 'foo',
        ];

        $query = array_filter(array_merge([
            'username' => $username,
            'password' => $password,
        ], [
            'sendtime ' => empty($params['sendTime']) === false ? Carbon::parse($params['sendTime'])
                ->format('YmdHis') : null,
            'mobile' => $params['to'],
            'message' => $params['text'],
        ]));

        $messageFactory->expects('createRequest')->with(
            'POST',
            'http://api.twsms.com/smsSend.php',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($query)
        )->andReturns(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->expects('sendRequest')->with($request)->andReturns(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->expects('getBody->getContents')->andReturns(
            $content = '
                <smsResp>
                    <code>00000</code>
                    <text>Success</text>
                    <msgid>265078525</msgid>
                </smsResp>
            '
        );

        $this->assertSame([
            'code' => '00000',
            'text' => 'Success',
            'msgid' => '265078525',
        ], $client->send($params));
    }

    public function testSendFail()
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('手機號碼格式錯誤');
        $client = new Client(
            $username = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );
        $params = [
            'to' => 'foo',
            'text' => 'foo',
        ];

        $query = array_filter(array_merge([
            'username' => $username,
            'password' => $password,
        ], [
            'sendtime ' => empty($params['sendTime']) === false ? Carbon::parse($params['sendTime'])
                ->format('YmdHis') : null,
            'mobile' => $params['to'],
            'message' => $params['text'],
        ]));

        $messageFactory->expects('createRequest')->with(
            'POST',
            'http://api.twsms.com/smsSend.php',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($query)
        )->andReturns(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->expects('sendRequest')->with($request)->andReturns(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->expects('getBody->getContents')->andReturns(
            $content = '
                <smsResp>
                    <code>00100</code>
                    <text>mobile tag error</text>
                </smsResp>
            '
        );

        $client->send($params);
    }
}
