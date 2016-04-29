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
 * @package EventbritePHP
 * @todo    Add a batch method.
 * @todo    Allow the token to be passed as a query string.
 */
class Eventbrite
{
    /**
     * The current version of this library.
     */
    const VERSION = '0.1';

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
        $config = array_merge($config, $default_config);
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
            $body = isset($options['body']) ? $options['body'] : [];
            $pv = isset($options['protocol_version']) ? $options['protocol_version'] : '1.1';
            // Make the request.
            $request = new Request($verb, $endpoint, $headers, $body, $pv);
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
     * @param       $verb
     * @param       $endpoint
     * @param null  $params
     * @param null  $body
     * @param null  $headers
     * @param array $options
     *
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function makeRequest($verb, $endpoint, $params = null, $body = null, $headers = null, $options = [])
    {
        // Merge the query string.
        if ($params !== null) {
            if (!isset($options['query'])) {
                $options['query'] = [];
            }
            $options['query'] = array_merge($options['query'], $params);
        }
        // We can only have one body, so overwrite it if it exists.
        if ($body !== null) {
            $options['body'] = $body;
        }
        // Merge the headers.
        if ($headers !== null) {
            if (!isset($options['headers'])) {
                $options['headers'] = [];
            }
            $options['headers'] = array_merge($options['headers'], $headers);
        }
        return $this->call($verb, $endpoint, $options);
    }

    /**
     * Checks if the HTTP method being used is correct.
     *
     * @param $http_method
     *
     * @return bool
     */
    public function validMethod($http_method)
    {
        $valid_methods = ['get', 'post', 'put', 'patch', 'delete'];
        if (in_array(strtolower($http_method), $valid_methods)) {
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
    private function parseResponse(ResponseInterface $response)
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
     * Wrapper shortcut on the makeRequest method for "GET" requests.
     *
     * @param       string $endpoint
     * @param array        $options
     *
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function get($endpoint, $params = null, $body = null, $headers = null, $options = [])
    {
        return $this->makeRequest('GET', $endpoint, $params, $body, $headers, $options);
    }

    /**
     * Wrapper shortcut on the makeRequest method for "POST" requests.
     *
     * @param       $endpoint
     * @param array $options
     *
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function post($endpoint, $params = null, $body = null, $headers = null, $options = [])
    {
        return $this->makeRequest('POST', $endpoint, $params, $body, $headers,
          $options);
    }

    /**
     * Wrapper shortcut on the makeRequest method for "PUT" requests.
     *
     * @param       $endpoint
     * @param array $options
     *
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function put($endpoint, $params = null, $body = null, $headers = null, $options = [])
    {
        return $this->makeRequest('PUT', $endpoint, $params, $body, $headers, $options);
    }

    /**
     * Wrapper shortcut on the makeRequest method for "PATCH" requests.
     *
     * @param       $endpoint
     * @param array $options
     *
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function patch($endpoint, $params = null, $body = null, $headers = null, $options = [])
    {
        return $this->makeRequest('PATCH', $endpoint, $params, $body, $headers, $options);
    }

    /**
     * Wrapper shortcut on the makeRequest method for "DELETE" requests.
     *
     * @param       $endpoint
     * @param array $options
     *
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function delete($endpoint, $params = null, $body = null, $headers = null, $options = [])
    {
        return $this->makeRequest('DELETE', $endpoint, $params, $body, $headers, $options);
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
