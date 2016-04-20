# Eventbrite PHP
PHP integration for the Eventbrite API.


This API does not deal with OAuth authentication. It assumes that your
app has already been authenticated and that you have an OAuth token or 
tokens. It is this OAuth token that should be passed to the class.

By default this library uses Guzzle 6 to connect to the Eventbrite API.
If you wish to use something else that follows the ClientInterface this 
can be set using $handler->setClient($class).