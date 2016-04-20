<?php
/**
 * @file
 * The base class for EventbritePHP.
 */
namespace JamieHollern\Eventbrite;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use JamieHollern\Eventbrite\Handler\Guzzle6Handler;
use GuzzleHttp\Exception\BadResponseException;

/**
 * Class Eventbrite
 *
 * @package EventbritePHP
 */
class Eventbrite
{
    /**
     * The current version of this library.
     */
    const VERSION = '0.1';

    /**
     * @var \JamieHollern\Eventbrite\Handler\Guzzle6Handler
     */
    private $handler;

    /**
     * @param                                  $token
     * @param array                            $config
     * @param \GuzzleHttp\ClientInterface|null $client
     */
    public function __construct($token, $config = [], ClientInterface $client = null)
    {
        $this->handler = new Guzzle6Handler($token, $config);
        if ($client) {
            $this->handler->setClient($client);
        }
    }

    /**
     * Make the call to Eventbrite, either synchronously or asynchonously.
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
        $request = new Request($http_method, $endpoint, $headers, $body,
          $protocol_version);
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
              $request);
        }
    }

    public function makeRequest() {
        $body = $request->getBody();
        return [
          'code' => $request->getStatusCode(),
          'headers' => $request->getHeaders(),
          'body' => $request->getBody()->getContents(),
        ];
    }

    /**
     * @todo Add a batch method.
     */
}
