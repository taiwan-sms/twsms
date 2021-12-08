# TwSMS notifications channel for Laravel 5.3+

[![StyleCI](https://styleci.io/repos/83760327/shield?style=flat)](https://styleci.io/repos/83760327)
[![Build Status](https://travis-ci.org/taiwan-sms/twsms.svg)](https://travis-ci.org/taiwan-sms/twsms)
[![Total Downloads](https://poser.pugx.org/taiwan-sms/twsms/d/total.svg)](https://packagist.org/packages/taiwan-sms/twsms)
[![Latest Stable Version](https://poser.pugx.org/taiwan-sms/twsms/v/stable.svg)](https://packagist.org/packages/taiwan-sms/twsms)
[![Latest Unstable Version](https://poser.pugx.org/taiwan-sms/twsms/v/unstable.svg)](https://packagist.org/packages/taiwan-sms/twsms)
[![License](https://poser.pugx.org/taiwan-sms/twsms/license.svg)](https://packagist.org/packages/taiwan-sms/twsms)
[![Monthly Downloads](https://poser.pugx.org/taiwan-sms/twsms/d/monthly)](https://packagist.org/packages/taiwan-sms/twsms)
[![Daily Downloads](https://poser.pugx.org/taiwan-sms/twsms/d/daily)](https://packagist.org/packages/taiwan-sms/twsms)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/taiwan-sms/twsms/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/taiwan-sms/twsms/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/taiwan-sms/twsms/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/taiwan-sms/twsms/?branch=master)

This package makes it easy to send notifications using [twsms] with Laravel 5.3+.

## Contents

- [Installation](#installation)
    - [Setting up the TwSMS service](#setting-up-the-TwSMS-service)
- [Usage](#usage)
    - [Available Message methods](#available-message-methods)
- [Changelog](#changelog)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)

## Installation

You can install the package via composer:

```bash
composer require taiwan-sms/twsms illuminate/notifications php-http/guzzle6-adapter
```

Then you must install the service provider:

```php
// config/app.php
'providers' => [
    ...
    TaiwanSms\TwSMS\TwSMSServiceProvider::class,
],
```

### Setting up the TwSMS service

Add your TwSMS login, secret key (hashed password) and default sender name (or phone number) to
your `config/services.php`:

```php
// config/services.php
...
'twsms' => [
    'username' => env('SERVICES_TWSMS_USERNAME'),
    'password' => env('SERVICES_TWSMS_PASSWORD'),
],
...
```

## Usage

You can use the channel in your `via()` method inside the notification:

```php
use TaiwanSms\TwSMS\TwSMSMessage;
use TaiwanSms\TwSMS\TwSMSChannel;
use Illuminate\Notifications\Notification;

class AccountApproved extends Notification
{
    public function via($notifiable)
    {
        return [TwSMSChannel::class];
    }

    public function toTwSMS($notifiable)
    {
        return TwSMSMessage::create("Task #{$notifiable->id} is complete!");
    }
}
```

In your notifiable model, make sure to include a routeNotificationForTwSMS() method, which return the phone number.

```php
public function routeNotificationForTwSMS()
{
    return $this->phone;
}
```

### Available methods

`content()`: Sets a content of the notification message.

`sendTime()`: Set send time of the notification message.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email recca0120@gmail.com instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [JhaoDa](https://github.com/recca0120)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

# API Only

```bash
composer require taiwan-sms/twsms php-http/guzzle6-adapter
```

## How to use

```php
require __DIR__.'/vendor/autoload.php';

use TaiwanSms\TwSMS\Client;

$userId = 'xxx';
$password = 'xxx';

$client = new Client($userId, $password);

var_dump($client->credit()); // 取得額度
var_dump($client->send([
    'to' => '09xxxxxxxx',
    'text' => 'test message',
]));
/*
return [
    'code' => '00000',
    'text' => 'Success',
    'msgid' => '265078525',
];
*/
```
