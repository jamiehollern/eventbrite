<?php
/**
 * @file
 * A lightweight wrapper for the Eventbrite API using Guzzle 6. Inspired by
 * drewm/mailchimp-api.
 */
namespace jamiehollern\eventbrite;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\ResponseInterface;
use Exception;

/**
 * Class Eventbrite
 *
 * @package jamiehollern\eventbrite
 * @todo    Add a batch method.
 * @todo    Allow the token to be passed as a query string.
 * @todo    Properly deal with errors/non-200 responses correctly?
 */
class Eventbrite
{
    /**
     * The current version of this library.
     */
    const VERSION = '1.0.1';

    /**
     * An array of valid HTTP verbs.
     */
    private static $valid_verbs = ['get', 'post', 'put', 'patch', 'delete'];

    /**
     * The API endpoint to get the current user's details.
     */
    const CURRENT_USER_ENDPOINT = 'users/me/';

    /**
     * @var string The Eventbrite OAuth token.
     */
    private $token;

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var \GuzzleHttp\Psr7\Request
     */
    private $last_request = null;

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    private $last_response = null;

    /**
     * @param string $token
     *   The OAuth token to authenticate the request
     * @param array  $config
     *   An array of Guzzle config options so that everything is configurable.
     *
     * @throws \Exception
     */
    public function __construct($token, $config = [])
    {
        $default_config = [
            'base_uri' => 'https://www.eventbriteapi.com/v3/',
            // Turn exceptions off so we can handle the responses ourselves.
            'exceptions' => false,
            'timeout' => 30,
        ];
        $config = array_merge($default_config, $config);
        // Add this last so it's always there and isn't overwritten.
        $config['headers']['User-Agent'] = 'jamiehollern\eventbrite v' . self::VERSION . ' ' . \GuzzleHttp\default_user_agent();
        if (!empty($token)) {
            $this->token = $token;
            // Set the authorisation header.
            $config['headers']['Authorization'] = 'Bearer ' . $this->token;
            $this->client = new Client($config);
        } else {
            throw new \Exception('An OAuth token is required to connect to the Eventbrite API.');
        }
    }

    /**
     * Make the call to Eventbrite, only synchronous calls at present.
     *
     * @param string $verb
     * @param string $endpoint
     * @param array  $options
     *
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function call($verb, $endpoint, $options = [])
    {
        if ($this->validMethod($verb)) {
            // Get the headers and body from the options.
            $headers = isset($options['headers']) ? $options['headers'] : [];
            $body = isset($options['body']) ? $options['body'] : null;
            $pv = isset($options['protocol_version']) ? $options['protocol_version'] : '1.1';
            // Make the request.
            $request = new Request($verb, $endpoint, $headers, $body, $pv);
            // Save the request as the last request.
            $this->last_request = $request;
            // Send it.
            $response = $this->client->send($request, $options);
            if ($response instanceof ResponseInterface) {
                // Set the last response.
                $this->last_response = $response;
                // If the caller wants the raw response, give it to them.
                if (isset($options['parse_response']) && $options['parse_response'] === false) {
                    return $response;
                }
                $parsed_response = $this->parseResponse($response);
                return $parsed_response;
            } else {
                // This only really happens when the network is interrupted.
                throw new BadResponseException('A bad response was received.',
                    $request);
            }
        } else {
            throw new \Exception('Unrecognised HTTP verb.');
        }
    }

    /**
     * A slightly abstracted wrapper around call().
     *
     * This essentially splits the call options array into different parameters
     * to make it more obvious to less advanced users what parameters can be
     * passed to the client.
     *
     * @param string $verb
     * @param string $endpoint
     * @param null   $params
     * @param null   $body
     * @param null   $headers
     * @param array  $options
     *
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function makeRequest($verb, $endpoint, $params = null, $body = null, $headers = null, $options = [])
    {
        // We can only have one body, so overwrite it if it exists.
        if ($body !== null) {
            $options['body'] = $body;
        }
        // Merge the mergeable arrays if necessary.
        $mergeable = [
            'query' => $params,
            'headers' => $headers,
        ];
        foreach ($mergeable as $key => $value) {
            if ($value !== null) {
                if (!isset($options[$key])) {
                    $options[$key] = [];
                }
                $options[$key] = array_merge($options[$key], $value);
            }
        }
        // Make the call.
        return $this->call($verb, $endpoint, $options);
    }

    /**
     * Checks if the HTTP method being used is correct.
     *
     * @param string $http_method
     *
     * @return bool
     */
    public function validMethod($http_method)
    {
        if (in_array(strtolower($http_method), self::$valid_verbs)) {
            return true;
        }
        return false;
    }

    /**
     * Parses the response from
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return array
     */
    public function parseResponse(ResponseInterface $response)
    {
        $body = $response->getBody()->getContents();
        return [
            'code' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => ($this->isValidJson($body)) ? json_decode($body,
                true) : $body,
        ];
    }

    /**
     * Checks a string to see if it's JSON. True if it is, false if it's not.
     *
     * @param string $string
     *
     * @return bool
     */
    public function isValidJson($string)
    {
        if (is_string($string)) {
            json_decode($string);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        return false;
    }

    /**
     * Checks if the class can connect to the Eventbrite API.
     *
     * Checks if we can connect to the API by calling the user endpoint and
     * checking the response code. If the response code is 2xx it returns true,
     * otherwise false.
     *
     * @return bool
     */
    public function canConnect()
    {
        $data = $this->get(self::CURRENT_USER_ENDPOINT);
        if (strpos($data['code'], '2') === 0) {
            return true;
        }
        return false;
    }

    /**
     * Provides shortcut methods named by HTTP verbs.
     *
     * Provides shortcut methods for GET, POST, PUT, PATCH and DELETE.
     *
     * @param string $method
     * @param array  $args
     *
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function __call($method, $args) {
        if ($this->validMethod($method)) {
            array_unshift($args, $method);
            return call_user_func_array(array($this, 'makeRequest'), $args);
        } else {
            throw new \BadMethodCallException('Method not found in class.');
        }
    }

    /**
     * Returns the last request object for inspection.
     *
     * @return \GuzzleHttp\Psr7\Request
     */
    public function getLastRequest()
    {
        return $this->last_request;
    }

    /**
     * Returns the last response object for inspection.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getLastResponse()
    {
        return $this->last_response;
    }

}
