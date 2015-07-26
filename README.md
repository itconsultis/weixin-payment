# weixin-payment

A sane PHP client for WeChat payments.

## What it does

The client exposes a clean interface to WeChat's payment-related web service
calls. It transparently handles boilerplate stuff like request signing and XML
serialization so you can focus on the important stuff.

## What it does NOT do

This package does not perform authentication; it will *not* help you get a user's
OpenID. Fortunately, there are plenty of other packages that already do this.
[overtrue/wechat](https://packagist.org/packages/overtrue/wechat) is a pretty good one.


## Usage

#### Obtaining a Client instance

```php
$client = \ITC\Weixin\Payment\Client::instance([
    'app_id' => 'your appid',
    'secret' => 'your signing secret',
    'mch_id' => 'your merchant id',
    'public_key_path' => '/path/to/public_key',
    'private_key_path' => '/path/to/private_key',
]);
```

#### Starting a payment

```php
// execute the "pay/unifiedorder" command; the result is a Message instance
$result = $client->command('pay/unifiedorder')->execute([
    'openid' => 'wx_9f8a98g9a8geag0',
    'trade_type' => 'JSAPI',
    'out_trade_no' => 'your-order-id',
    'total_fee' => 1000,
]);

// if a prepay_id is in the Message, the payment is ready to execute
if ($prepay_id = $result->get('prepay_id')
{
    // jsapize() returns a JsonSerializable object
    $jsbridge_params = $client->jsapize(['prepay_id'=>$prepay_id]);
}
```

The `$jsbridge_params` object can then be JSON-serialized and supplied directly
to the `WeixinJSBridge` global in the Javascript space. Here's an example:

```javascript
var jsbridge_params = <?php echo json_encode($jsbridge_params) ?>;

WeixinJSBridge.invoke('getBrandWCPayRequest', jsbridge_params, function(result) {
    // do something with the result
});
```

## Commands

- `pay/unifiedorder` [spec](https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_1)

    ```php
    $result = $client->command('pay/unifiedorder')->execute([
        'openid' => 'wx_9f8a98g9a8geag0',
        'trade_type' => 'JSAPI',
        'out_trade_no' => 'domain-order-id',
        'total_fee' => 1000,
    ]);
    ```

- `pay/orderquery` [spec](https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_2&index=4) (NOT IMPLEMENTED)

    ```php
    $result = $client->command('pay/orderquery')->execute([
        // ...
    ]);
    ```

- `pay/closeorder` [spec](https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_3&index=5) (NOT IMPLEMENTED)

    ```php
    $result = $client->command('pay/closeorder')->execute([
        // ...
    ]);
    ```

- `secapi/refund` [spec](https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_4&index=6) (NOT IMPLEMENTED)

    ```php
    $result = $client->command('secapi/refund')->execute([
        // ...
    ]);
    ```

- `pay/refundquery` [spec](https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_5&index=7) (NOT IMPLEMENTED)

    ```php
    $result = $client->command('pay/refundquery')->execute([
        // ...
    ]);
    ```

- `pay/downloadbill` [spec](https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_6&index=8) (NOT IMPLEMENTED)

    ```php
    $result = $client->command('pay/downloadbill')->execute([
        // ...
    ]);
    ```

- `payitil/report` [spec](https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_8&index=9) (NOT IMPLEMENTED)

    ```php
    $result = $client->command('payitil/report')->execute([
        // ...
    ]);
    ```

- `tools/shorturl` [spec](https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_9&index=10) (NOT IMPLEMENTED)

    ```php
    $result = $client->command('tools/shorturl')->execute([
        // ...
    ]);
    ```

## Messages

This library represents XML payloads transported between the client and the
WeChat web service as *messages*. A `Message` is an object that provides uniform
key/value access to the underlying data structure. More importantly it exposes
a dead simple interface for signing and signature verification.

```php
$message = $client->createMessage(['foo'=>1, 'bar'=>'two']);

// authenticate an unsigned message; returns boolean false
$message->authenticate(); 

// sign the message
$message->sign();

// authenticate a signed message; returns boolean true
$message->authenticate();
```

When you execute a command, you are actually getting back a `Message` that
can be authenticated at any time.

```php
$result = $client->command('pay/unifiedorder')->execute([/* ... */]);

// boolean true or false
$authentic = $result->authenticate();
```

## Installation

### Composer

    composer install itc/weixin-payment

### Laravel

The package ships with a [Laravel 5](http://laravel.com) service provider that
registers the Client on the application service container. Install the service
provider by adding the following line to the `providers`
array in `config/app.php`:

    ITC\Weixin\Payment\ServiceProvider::class

Then publish the package configuration via:

    php artisan vendor:publish

This publishes a configuration file in `config/weixin-payment.php`.

You can obtain the client instance using the service container:

```php
$client = App::make('ITC\Weixin\Payment\Contracts\Client');
```

As usual, you can also take advantage of dependency injection.

## Contributing

Feel free to fork this project and create a pull request!

#### How to run tests

    ./phpunit

## License

The MIT License (MIT)

Copyright (c) 2015 by IT Consultis Ltd.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
