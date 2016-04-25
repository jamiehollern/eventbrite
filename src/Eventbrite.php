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

    private $last_error = null;

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
          'headers' => [
            'User-Agent' => 'jamiehollern\eventbrite v' . self::VERSION . ' ' . \GuzzleHttp\default_user_agent(),
            'timeout' => 30,
          ],
          // Turn exceptions off so we can handle the responses ourselves.
          'exceptions' => false,
        ];
        $config = array_merge($config, $default_config);
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
     * @param       string $http_method
     * @param              $endpoint
     * @param array        $options
     *
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function call($http_method, $endpoint, $options = [])
    {
        if ($this->validMethod($http_method)) {
            // Get the headers and body from the options.
            $headers = isset($options['headers']) ? $options['headers'] : [];
            $body = isset($options['body']) ? $options['body'] : [];
            $pv = isset($options['protocol_version']) ? $options['protocol_version'] : '1.1';
            // Make the request.
            $request = new Request($http_method, $endpoint, $headers, $body,
              $pv);
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
     * Wrapper shortcut on the call method for "GET" requests.
     *
     * @param       string $endpoint
     * @param array        $options
     *
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function get($endpoint, $options = [])
    {
        return $this->call('GET', $endpoint, $options);
    }

    /**
     * Wrapper shortcut on the call method for "POST" requests.
     *
     * @param       $endpoint
     * @param array $options
     *
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function post($endpoint, $options = [])
    {
        return $this->call('POST', $endpoint, $options);
    }

    /**
     * Wrapper shortcut on the call method for "PUT" requests.
     *
     * @param       $endpoint
     * @param array $options
     *
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function put($endpoint, $options = [])
    {
        return $this->call('PUT', $endpoint, $options);
    }

    /**
     * Wrapper shortcut on the call method for "PATCH" requests.
     *
     * @param       $endpoint
     * @param array $options
     *
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function patch($endpoint, $options = [])
    {
        return $this->call('PATCH', $endpoint, $options);
    }

    /**
     * Wrapper shortcut on the call method for "DELETE" requests.
     *
     * @param       $endpoint
     * @param array $options
     *
     * @return array|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function delete($endpoint, $options = [])
    {
        return $this->call('DELETE', $endpoint, $options);
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
