<?php

namespace TaiwanSms\TwSMS;

use Carbon\Carbon;
use DomainException;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\MessageFactory;
use Psr\Http\Client\ClientExceptionInterface;

class Client
{
    /**
     * $apiEndpoint.
     *
     * @var string
     */
    public $apiEndpoint = 'http://api.twsms.com/';

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * __construct.
     *
     * @param string $username
     * @param string $password
     * @param HttpClient $httpClient
     * @param MessageFactory $messageFactory
     */
    public function __construct($username, $password, HttpClient $httpClient = null, MessageFactory $messageFactory = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->messageFactory = $messageFactory ?: MessageFactoryDiscovery::find();
    }

    /**
     * credit.
     *
     * @return int
     */
    public function credit()
    {
        $response = $this->query([
            'checkpoint' => 'Y',
        ]);

        return (int) $response['point'];
    }

    /**
     * query.
     *
     * @param array $params
     * @return array
     * @throws ClientExceptionInterface
     */
    public function query($params)
    {
        $response = $this->doRequest('smsQuery.php', array_filter(array_merge([
            'username' => $this->username,
            'password' => $this->password,
            'deltime' => null,
            'checkpoint' => null,
            'mobile' => null,
            'msgid' => null,
            'outrange' => null,
        ], $this->remapParams($params))));

        $response = $this->parseResponse($response);

        if ($this->isValidResponse($response) === false) {
            throw new DomainException($this->getErrorMessage($response), 500);
        }

        return $response;
    }

    /**
     * send.
     *
     * @param array $params
     * @return array
     * @throws ClientExceptionInterface
     */
    public function send($params)
    {
        $response = $this->doRequest('smsSend.php', array_filter(array_merge([
            'username' => $this->username,
            'password' => $this->password,
            'sendtime ' => null,
            'expirytime' => null,
            'popup' => null,
            'mo' => null,
            'mobile' => '',
            'longsms' => null,
            'message' => '',
            'drurl' => null,
        ], $this->remapParams($params))));

        $response = $this->parseResponse($response);

        if ($this->isValidResponse($response) === false) {
            throw new DomainException($this->getErrorMessage($response), 500);
        }

        return $response;
    }

    /**
     * isValidResponse.
     *
     * @param array $response
     *
     * @return bool
     */
    private function isValidResponse($response)
    {
        if (empty($response['code']) === true) {
            return false;
        }

        return in_array($response['code'], ['00000', '00001'], true) === true;
    }

    /**
     * doRequest.
     *
     * @param string $uri
     * @param array $params
     * @return string
     * @throws ClientExceptionInterface
     */
    private function doRequest($uri, $params)
    {
        $request = $this->messageFactory->createRequest(
            'POST',
            rtrim($this->apiEndpoint, '/').'/'.$uri,
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($params)
        );
        $response = $this->httpClient->sendRequest($request);

        return $response->getBody()->getContents();
    }

    /**
     * remapParams.
     *
     * @param array $params
     * @return array
     */
    private function remapParams($params)
    {
        if (empty($params['to']) === false) {
            $params['mobile'] = $params['to'];
            unset($params['to']);
        }

        if (empty($params['text']) === false) {
            $params['message'] = $params['text'];
            unset($params['text']);
        }

        if (empty($params['sendTime']) === false) {
            $params['sendtime'] = Carbon::parse($params['sendTime'])->format('YmdHis');
            unset($params['sendTime']);
        }

        return $params;
    }

    /**
     * parseResponse.
     *
     * @param string $response
     * @return array
     */
    private function parseResponse($response)
    {
        $tags = [
            'code',
            'text',
            'msgid',
            'mobile',
            'statuscode',
            'statustext',
            'donetime',
            'username',
            'point',
        ];

        $result = [];

        foreach ($tags as $tag) {
            if ((bool) preg_match('/<'.$tag.'>(?P<value>.+)<\/'.$tag.'>/', $response, $match) === false) {
                continue;
            }

            $result[$tag] = html_entity_decode($match['value']);
        }

        return $result;
    }

    /**
     * getErrorMessage.
     *
     * @param array $response
     */
    private function getErrorMessage($response)
    {
        $messages = [
            '00000' => '完成',
            '00001' => '狀態尚未回復',
            '00010' => '帳號或密碼錯誤',
            '00020' => '通數不足',
            '00030' => 'IP 無使用權限',
            '00040' => '帳號已停用',
            '00050' => 'sendtime 格式錯誤',
            '00060' => 'expirytime 格式錯誤',
            '00070' => 'popup 格式錯誤',
            '00080' => 'mo 格式錯誤',
            '00090' => 'longsms 格式錯誤',
            '00100' => '手機號碼格式錯誤',
            '00110' => '沒有簡訊內容',
            '00120' => '長簡訊不支援國際門號',
            '00130' => '簡訊內容超過長度',
            '00140' => 'drurl 格式錯誤',
            '00150' => 'sendtime 預約的時間已經超過',
            '00300' => '找不到 msgid',
            '00310' => '預約尚未送出',
            '00400' => '找不到 snumber 辨識碼',
            '00410' => '沒有任何 mo 資料',
            '99998' => '資料處理異常，請重新發送',
            '99999' => '系統錯誤，請通知系統廠商',
        ];

        if (isset($messages[$response['code']]) === true) {
            return $messages[$response['code']];
        }

        return empty($response['text']) === false ? $response['text'] : 'Unknown';
    }
}
