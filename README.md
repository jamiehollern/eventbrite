# Eventbrite PHP
A lightweight PHP wrapper for the [Eventbrite API v3](https://www.eventbrite.co.uk/developer/v3/ "Eventbrite API developer docs") using Guzzle 6.

[![Build Status](https://travis-ci.org/jamiehollern/eventbrite.svg?branch=master)](https://travis-ci.org/jamiehollern/eventbrite)
[![Coverage Status](https://coveralls.io/repos/jamiehollern/eventbrite/badge.svg?branch=master&service=github)](https://coveralls.io/github/jamiehollern/eventbrite?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jamiehollern/eventbrite/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jamiehollern/eventbrite/?branch=master)
[![Packagist](https://img.shields.io/packagist/dt/jamiehollern/eventbrite.svg?maxAge=2592000)](https://packagist.org/packages/jamiehollern/eventbrite)

# Requirements
* PHP >= 5.5
* cURL if you don't want any extra config (see the [Guzzle docs](http://docs.guzzlephp.org/en/latest/faq.html#does-guzzle-require-curl))

# Installation
Install via Composer command:  
`composer require jamiehollern/eventbrite`

Install via composer.json:

```
{
  "require": {
    "jamiehollern/eventbrite": "1.0.0"
  }
}
```
Once you've done this:

* Run `composer install`
* Add the autoloader to your script `require_once('vendor/autoload.php')`

# Authentication

This API does not deal with the OAuth authentication flow. It assumes that your app has already been authenticated and that you have an OAuth token for any accounts you're going to use. It is this OAuth token that should be passed to the class on instantiation. 

You can find out more about the Eventbrite authentication process on the [Eventbrite API docs](https://www.eventbrite.co.uk/developer/v3/reference/authentication/). If you only have a single app to authenticate you can simply get a premade OAuth token by adding an app on your [Eventbrite apps page](https://www.eventbrite.co.uk/myaccount/apps/).

# Examples

## Instantiating the class
### Basic usage
To get started, you only need an OAuth token to pass into the Eventbrite class:

```php
<?php

use jamiehollern/eventbrite/Eventbrite;

$eventbrite = new Eventbrite('MY_OAUTH_TOKEN');

?>
```

You can check that everything's working okay by running:

```php
<?php

use jamiehollern/eventbrite/Eventbrite;

$eventbrite = new Eventbrite('MY_OAUTH_TOKEN');
// Returns true if you can connect.
$can_connect = $eventbrite->canConnect();

?>
```

### Advanced options
You can take advantage of the fact that this library is a fairly light wrapper around Guzzle if you need more advanced options while connecting.

To increase the timeout limit from the default 30 seconds to 60 seconds:

```php
<?php

use jamiehollern/eventbrite/Eventbrite;

$eventbrite = new Eventbrite('MY_OAUTH_TOKEN', ['timeout' => 60]);

?>
```

If you don't have cURL installed, you can add a different HTTP request handler when instantiating the class. See the [Guzzle docs](http://docs.guzzlephp.org/en/latest/handlers-and-middleware.html) for more information.

```php
<?php

use jamiehollern/eventbrite/Eventbrite;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\StreamHandler;

$handler = new StreamHandler();
$stack = HandlerStack::create($handler);
$eventbrite = new Eventbrite('MY_OAUTH_TOKEN', ['handler' => $stack]);

?>
```

## Making requests

There are three ways of making requests with the library and all of them roughly amount to the same thing, but with various levels of abstraction:

* `makeRequest`
* `call`
* `get/post/put/patch/delete`

Each of the following examples make the same call, using [Eventbrite expansions](https://www.eventbrite.co.uk/developer/v3/reference/expansions/).


### The `makeRequest` method

This method is a wrapper around the `call` method and differs only in the parameters it takes. It is designed to be slightly more obvious than `call` in that the parameters explicitly outline what to do with the method.

```php
<?php

use jamiehollern/eventbrite/Eventbrite;

$eventbrite = new Eventbrite('MY_OAUTH_TOKEN');

// Get all of the current users' ticket orders and ensure that the event 
// data and the event venue data are present in full.
$eventbrite->makeRequest('GET', 'users/me/orders/', ['expand' => 'event.venue']);

?>
```

### The shortcut methods
The shortcut methods are simply methods named for the HTTP verbs and are identical to the `makeRequest` method but for the fact that the first parameter of `makeRequest` is the actual name of the shortcut method. The methods available are `get`, `post`, `put`, `patch` and `delete`.

```php
<?php

use jamiehollern/eventbrite/Eventbrite;

$eventbrite = new Eventbrite('MY_OAUTH_TOKEN');

// Get all of the current users' ticket orders and ensure that the event 
// data and the event venue data are present in full.
$events = $eventbrite->get('users/me/orders/', ['expand' => 'event.venue']);

?>
```
### The `call` method

The `call` method is the most lightweight wrapper around the Guzzle client and takes three parameters; the HTTP verb (i.e. GET, POST etc), the endpoint and an optional array config for the request that maps directly to the [Guzzle request options](http://docs.guzzlephp.org/en/latest/request-options.html). 

```php
<?php

use jamiehollern/eventbrite/Eventbrite;

$eventbrite = new Eventbrite('MY_OAUTH_TOKEN');

// Get all of the current users' ticket orders and ensure that the event 
// data and the event venue data are present in full.
$eventbrite->call('GET', 'users/me/orders/', ['query' => ['expand' => 'event.venue']]);

?>
```

## Responses
Successfull requests made to the API will return an array with the following information:

* Response code
* Response headers
* Response body

If the body content sent back is JSON (which is almost always will be with the Eventbrite API) then this will be decoded to a multidimensional array.

The library does this by taking a [Guzzle response object](http://docs.guzzlephp.org/en/latest/quickstart.html#using-responses) and extracting the most pertinent data from it. This is useful enough for most requests but since the library is just a wrapper around Guzzle, if you wish you can request the full Guzzle response object instead by passing a `false` value for the `parse_response` parameter.

```php
<?php

use jamiehollern/eventbrite/Eventbrite;

$eventbrite = new Eventbrite('MY_OAUTH_TOKEN');

// Make a request but instruct the call to not parse the response
// and instead return the Guzzle response object.
$eventbrite->call('GET', 'users/me/orders/', ['parse_response' => false]);

?>
```

If you'd like the response object but also want the usual array or don't want to modify your requests, the library stores the last request for you.

```php
<?php

use jamiehollern/eventbrite/Eventbrite;

$eventbrite = new Eventbrite('MY_OAUTH_TOKEN');

// Make a request as normal.
$eventbrite->call('GET', 'users/me/orders/');
// Get the response object.
$last_response = $eventbrite->getLastResponse();

?>
```

If you need the parsed data again you can parse the last response. Be advised that the `parseResponse` method expects an object that implements the Guzzle `ResponseInterface`, which will always be a Guzzle `response` object in this case.

```php
<?php

use jamiehollern/eventbrite/Eventbrite;

$eventbrite = new Eventbrite('MY_OAUTH_TOKEN');

// Make a request as normal.
$eventbrite->call('GET', 'users/me/orders/');
// Get the response object.
$last_response = $eventbrite->getLastResponse();
// Parse the response.
$parsed_response = $eventbrite->parseResponse($last_response);

?>
```

## FAQ
* __My calls aren't working and don't seem to be calling the correct endpoint. What's wrong?__ Make sure you don't put a slash `/` in front of your endpoint or you'll confuse Guzzle.
* __Is there more in depth documentation available?__ Not yet but as I develop the library I plan to write extensive documentation.
* __What is the roadmap for this library?__ Initially it will serve to be a lightweight abstraction around Guzzle focused on RESTful requests to the Eventbrite API but eventually the plan is to include data objects and heavier abstraction on a per endpoint basis for users who don't want to put too much effort into using the API manually. This will allow for both beginners and advanced users to find something valuable in the library.

