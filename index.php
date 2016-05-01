<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set('opcache.enable', 0);


include_once('vendor/autoload.php');

$eventbrite = new \jamiehollern\eventbrite\Eventbrite('DTLIWIDFR5B2ZXIBXV5J');
krumo($eventbrite);

/*$can_connect = $eventbrite->canConnect();

krumo($can_connect);*/

// Old format.
//$events = $eventbrite->get('users/me/orders/', ['query' => ['expand' => 'event.venue']]);

$events = $eventbrite->get('users/me/orders/', ['expand' => 'event.venue']);

krumo($events);

$eventbrite->test();
