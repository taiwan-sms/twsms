<?php

namespace TaiwanSms\TwSMS;

use Illuminate\Notifications\Notification;

class TwSMSChannel
{
    /**
     * $client.
     *
     * @var \TaiwanSms\TwSMS\Client
     */
    protected $client;

    /**
     * __construct.
     *
     * @param \TaiwanSms\TwSMS\Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     * @return array|string|void
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $to = $notifiable->routeNotificationFor('TwSMS')) {
            return;
        }

        $message = $notification->toTwSMS($notifiable);

        if (is_string($message)) {
            $message = new TwSMSMessage($message);
        }

        return $this->client->send([
            'to' => $to,
            'text' => trim($message->content),
        ]);
    }
}
