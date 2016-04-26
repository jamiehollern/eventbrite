# Eventbrite PHP
A lightweight PHP wrapper for the [Eventbrite API v3](https://www.eventbrite.co.uk/developer/v3/ "Eventbrite API developer docs") using Guzzle 6.

[![Build Status](https://travis-ci.org/jamiehollern/eventbrite.svg?branch=master)](https://travis-ci.org/jamiehollern/eventbrite)
[![Coverage Status](https://coveralls.io/repos/jamiehollern/eventbrite/badge.svg?branch=master&service=github)](https://coveralls.io/github/jamiehollern/eventbrite?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jamiehollern/eventbrite/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/jamiehollern/eventbrite/?branch=master)

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
    "php": ">=5.5",
    "jamiehollern/eventbrite": "*"
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

Coming soon.






