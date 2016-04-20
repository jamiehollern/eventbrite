<?php
/**
 * @file
 * Class to handle HTTP requests.
 */
namespace JamieHollern\Eventbrite\Handler;

use GuzzleHttp\ClientInterface;
use Exception;


/**
 * Class Guzzle6Handler
 * A request handler that sends PSR-7-compatible requests with Guzzle 6.
 *
 * @package EventbritePHP\Handler
 */
class Guzzle6Handler
{

    /**
     * @var string OAuth token
     */
    private $token;

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

    /**
     * Construct a client with the OAuth token. Allows config to be overriden.
     *
     * @param       $token
     * @param array $config
     *
     * @throws \Exception
     */
    public function __construct($token, $config = [])
    {
        $default_config = [
          'base_uri' => 'https://www.eventbriteapi.com/v3/',
          'headers' => [
            'User-Agent' => 'JamieHollern\Eventbrite' . \JamieHollern\Eventbrite\Eventbrite::VERSION . ' ' . \GuzzleHttp\default_user_agent(),
            'timeout' => 30,
            'exceptions' => true,
          ],
        ];
        $config = array_merge($config, $default_config);
        if (!empty($token)) {
            $this->token = $token;
            // Set the authorisation header.
            $config['headers']['Authorization'] = 'Bearer ' . $this->token;
        } else {
            throw new Exception('An OAuth token is required to connect to the Eventbrite API.');
        }
    }

    /**
     * Setter to allow the overriding of the Guzzle client to something else.
     *
     * @param \GuzzleHttp\ClientInterface $client
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
    }

}
