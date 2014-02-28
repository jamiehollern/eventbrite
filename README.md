Eventbrite PHP
==============

A PHP class for the new version (v3) of the Eventbrite API. To use this you
need to register at http://www.eventbrite.com and then create an app at
http://www.eventbrite.com/myaccount/apps/. Once you have an app it will 
provide you with an OAuth token. You need this token to connect to the 
Eventbrite API.

The methods for the API can be found at http://developer.eventbrite.com/docs/
This class works by taking the first part of the URI after "v3" as the method 
and the rest as parameters in an array. For example, if you were looking to 
call https://www.eventbriteapi.com/v3/events/123456789/attendees you would
call this: eventbrite::events(array('id' => 1234567890, 'data' => 'attendees');

Examples
========

```php
<?php

  // Load the Eventbrite API class.
  require_once('eventbrite.class.inc');

  // Instantiate a new object with your OAuth token.
  $eventbrite = new eventbrite('EVENTBRITEOATHTOKEN');

  // Get an event by its ID.
  $args = array('id' => 1234567890);
  $events = $eventbrite->events($args);

  // Get the attendees of an event.
  $args = array('id' => 1234567890, 'data' => 'attendees');
  $attendees = $eventbrite->events($args);

  // Get orders for events associated with a specific user.
  $args = array('id' => 1234567, 'data' => 'owned_event_orders');
  $orders = $eventbrite->users($args);

?>

