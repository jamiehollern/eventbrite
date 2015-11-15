<?php
/**
 * @file
 * Class to handle HTTP requests.
 */
namespace EventbritePHP\Handler;

use EventbritePHP\Eventbrite;
use Exception;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use RuntimeException;
use GuzzleHttp\Promise;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

/**
 * Class Guzzle6Handler
 * A request handler that sends PSR-7-compatible requests with Guzzle 6.
 *
 * @package EventbritePHP\Handler
 */
class Guzzle6Handler
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

    /**
     * Construct a client with an injected object or use the default.
     *
     * @param array                            $config
     * @param \GuzzleHttp\ClientInterface|null $client
     *
     * @throws \Exception
     */
    public function __construct($config = [], ClientInterface $client = null)
    {
        if ($client !== null) {
            // Only default client objects are passed the config so injected
            // client objects must be pre-instantiated with config values.
            $this->client = $client;
        } else {
            $defaults = [
              'base_uri' => 'https://www.eventbriteapi.com/v3/',
              'headers' => [
                'User-Agent' => 'eventbritephp/' . Eventbrite::VERSION . ' ' . \GuzzleHttp\default_user_agent(),
                'timeout' => 30,
                'exceptions' => true,
              ],
            ];
            // Merge user config with defaults. Defaults is last so overridable.
            $config = array_merge($config, $defaults);
            // We need an OAuth token or we can't proceed.
            if (isset($config['token']) && $config['token']) {
                // Set the authorisation header.
                $config['headers']['Authorization'] = 'Bearer ' . $config['token'];
                unset($config['token']);
                // Instantiate.
                $this->client = new Client($config);
            } else {
                throw new Exception('An OAuth token is required to connect to the Eventbrite API.');
            }
        }
    }

    /**
     * Make the call to Eventbrite, either normally or asynchonously.
     *
     * @param       $http_method
     * @param       $endpoint
     * @param array $options
     *
     * @return \GuzzleHttp\Promise\PromiseInterface|mixed|\Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\BadResponseException
     */
    public function call($http_method, $endpoint, $options = [])
    {
        // Get the headers and body from the options.
        $headers = isset($options['headers']) ? $options['headers'] : [];
        $body = isset($options['body']) ? $options['body'] : [];
        $protocol_version = isset($options['protocol_version']) ? $options['protocol_version'] : '1.1';
        // Make the request.
        $request = new Request($http_method, $endpoint, $headers, $body, $protocol_version);
        // Send it.
        if (isset($options['async']) && $options['async']) {
            $response = $this->client->sendAsync($request, $options);
        } else {
            $response = $this->client->send($request, $options);
        }
        if ($response instanceof Response) {
            return $response;
        } else {
            throw new BadResponseException('A bad response was received from the server.',
              $request, $response);
        }
    }

    /**
     * Allows quick calls using shorthand verb methods such as $object->get().
     *
     * @param $method
     * @param $args
     *
     * @return \GuzzleHttp\Promise\PromiseInterface|mixed|\Psr\Http\Message\ResponseInterface
     *
     * @throws \RuntimeException
     */
    public function __call($method, $args)
    {
        // Define valid HTTP methods.
        $valid_methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        // Get the HTTP method we want to call.
        $filter = array_filter($valid_methods,
          function ($valid_method) use ($method) {
              return strtoupper($method) == $valid_method;
          });
        $http_method = reset($filter);
        if ($http_method) {
            $endpoint = array_shift($args);
            $options = array_shift($args);
            return $this->call($http_method, $endpoint, $options);
        } else {
            throw new RuntimeException('Unknown HTTP verb used for method.');
        }
    }

}
