<?php
/**
 * @file
 * Unit tests for the Eventbrite class.
 */
namespace jamiehollern\eventbrite\Tests;

use GuzzleHttp\Exception\ConnectException;
use jamiehollern\eventbrite\Eventbrite;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use Mockery;

/**
 * Class EventbriteTest
 *
 * @package jamiehollern\eventbrite\Tests
 */
class EventbriteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \jamiehollern\eventbrite\Eventbrite
     */
    private $eventbrite;

    public function setUp()
    {
        $this->eventbrite = new Eventbrite('token');
    }

    /**
     * @covers jamiehollern\eventbrite\Eventbrite::__construct
     */
    public function testEventbriteNoToken()
    {
        $this->setExpectedException('Exception');
        $eventbrite = new Eventbrite('');
    }

    /**
     * @covers jamiehollern\eventbrite\Eventbrite::__construct
     */
    public function testEventbrite()
    {
        $eventbrite = new Eventbrite('valid_token');
        $this->assertInstanceOf('jamiehollern\eventbrite\Eventbrite',
          $eventbrite);
    }

    /**
     * @covers jamiehollern\eventbrite\Eventbrite::call
     * @covers jamiehollern\eventbrite\Eventbrite::validMethod
     */
    public function testCallBadVerb()
    {
        $this->setExpectedException('\Exception');
        $eventbrite = new Eventbrite('valid_token');
        $call = $eventbrite->call('PUNT', 'endpoint');
        $this->assertInstanceOf('jamiehollern\eventbrite\Eventbrite',
          $eventbrite);
    }

    /**
     * Generic test of call().
     *
     * @covers jamiehollern\eventbrite\Eventbrite::get
     * @covers jamiehollern\eventbrite\Eventbrite::call
     * @covers jamiehollern\eventbrite\Eventbrite::validMethod
     * @covers jamiehollern\eventbrite\Eventbrite::parseResponse
     * @covers jamiehollern\eventbrite\Eventbrite::isValidJson
     */
    public function testGet()
    {
        // Create a mock and queue a response.
        $mock = new MockHandler([
          new Response(200, ['Content-Type' => 'application/json'],
            '{ "test": "json" }'),
        ]);
        $handler = HandlerStack::create($mock);
        // Inject the mock handler into the config when instantiating.
        $eventbrite = new Eventbrite('valid_token', ['handler' => $handler]);
        $call = $eventbrite->get('endpoint');
        $expected = [
          'code' => 200,
          'headers' => ['Content-Type' => ['application/json']],
          'body' => ['test' => 'json'],
        ];
        $this->assertEquals($expected, $call);
    }

    /**
     * Returns non-JSON.
     *
     * @covers jamiehollern\eventbrite\Eventbrite::post
     * @covers jamiehollern\eventbrite\Eventbrite::call
     * @covers jamiehollern\eventbrite\Eventbrite::validMethod
     * @covers jamiehollern\eventbrite\Eventbrite::parseResponse
     * @covers jamiehollern\eventbrite\Eventbrite::isValidJson
     */
    public function testPost()
    {
        // Create a mock and queue a response.
        $mock = new MockHandler([
          new Response(201, ['Content-Type' => 'text/html'], '<html></html>'),
        ]);
        $handler = HandlerStack::create($mock);
        // Inject the mock handler into the config when instantiating.
        $eventbrite = new Eventbrite('valid_token', ['handler' => $handler]);
        $call = $eventbrite->post('endpoint');
        $expected = [
          'code' => 201,
          'headers' => ['Content-Type' => ['text/html']],
          'body' => '<html></html>',
        ];
        $this->assertEquals($expected, $call);
    }

    /**
     * Throws a request exception.
     *
     * @covers jamiehollern\eventbrite\Eventbrite::put
     * @covers jamiehollern\eventbrite\Eventbrite::call
     * @covers jamiehollern\eventbrite\Eventbrite::validMethod
     */
    public function testPut()
    {
        $this->setExpectedException('\GuzzleHttp\Exception\RequestException');
        // Create a mock and queue a response.
        $mock = new MockHandler([
          new RequestException("Error Communicating with Server",
            new Request('PUT', 'endpoint')),
        ]);
        $handler = HandlerStack::create($mock);
        // Inject the mock handler into the config when instantiating.
        $eventbrite = new Eventbrite('valid_token', ['handler' => $handler]);
        $call = $eventbrite->put('endpoint');
    }

    /**
     * Returns an unparsed response.
     *
     * @covers jamiehollern\eventbrite\Eventbrite::patch
     * @covers jamiehollern\eventbrite\Eventbrite::call
     * @covers jamiehollern\eventbrite\Eventbrite::validMethod
     */
    public function testPatch()
    {
        // Create a mock and queue a response.
        $mock = new MockHandler([
          new Response(200, ['Content-Type' => 'application/json'],
            '{ "test": "json" }'),
        ]);
        $handler = HandlerStack::create($mock);
        // Inject the mock handler into the config when instantiating.
        $eventbrite = new Eventbrite('valid_token', ['handler' => $handler]);
        $call = $eventbrite->patch('endpoint', ['parse_response' => false]);
        $this->assertInstanceOf('\GuzzleHttp\Psr7\Response', $call);
    }

    /**
     * Throws a connect exception.
     *
     * @covers jamiehollern\eventbrite\Eventbrite::delete
     * @covers jamiehollern\eventbrite\Eventbrite::call
     * @covers jamiehollern\eventbrite\Eventbrite::validMethod
     * @covers jamiehollern\eventbrite\Eventbrite::parseResponse
     * @covers jamiehollern\eventbrite\Eventbrite::isValidJson
     */
    public function testDelete()
    {
        $this->setExpectedException('\GuzzleHttp\Exception\ConnectException');
        // Create a mock and queue a response.
        $mock = new MockHandler([
          new ConnectException('test', new Request('DELETE', 'endpoint')),
        ]);
        $handler = HandlerStack::create($mock);
        // Inject the mock handler into the config when instantiating.
        $eventbrite = new Eventbrite('valid_token', ['handler' => $handler]);
        $call = $eventbrite->delete('endpoint');
        $expected = [
          'code' => 404,
          'headers' => ['Content-Type' => ['application/json']],
          'body' => ['error' => 'Resource not found.'],
        ];
        $this->assertEquals($expected, $call);
    }

    /**
     * Throws a connect exception.
     *
     * @covers jamiehollern\eventbrite\Eventbrite::get
     * @covers jamiehollern\eventbrite\Eventbrite::call
     * @covers jamiehollern\eventbrite\Eventbrite::validMethod
     */
    public function testNetworkError()
    {
        // This should throw a BadResponseException.
        $this->setExpectedException('\GuzzleHttp\Exception\BadResponseException');
        // Mock the Guzzle client.
        $mock = Mockery::mock('GuzzleHttp\Client');
        // Instantiate the class.
        $eventbrite = new Eventbrite('valid_token');
        // Use reflection to change the private property "client" to the mock.
        $reflection = new \ReflectionProperty(get_class($eventbrite), 'client');
        $reflection->setAccessible(true);
        $reflection->setValue($eventbrite, $mock);
        // Mock the $client->send method and return null.
        $mock->shouldReceive('send');
        // Call the method.
        $call = $eventbrite->get('endpoint');
    }

    /**
     * Returns an unparsed response.
     *
     * @covers jamiehollern\eventbrite\Eventbrite::getLastResponse
     * @covers jamiehollern\eventbrite\Eventbrite::get
     * @covers jamiehollern\eventbrite\Eventbrite::call
     * @covers jamiehollern\eventbrite\Eventbrite::validMethod
     */
    public function testGetLastResponse()
    {
        // Create a mock and queue a response.
        $mock = new MockHandler([
          new Response(200, ['Content-Type' => 'application/json'],
            '{ "test": "json" }'),
        ]);
        $handler = HandlerStack::create($mock);
        // Inject the mock handler into the config when instantiating.
        $eventbrite = new Eventbrite('valid_token', ['handler' => $handler]);
        $eventbrite->get('endpoint');
        $response = $eventbrite->getLastResponse();
        $this->assertInstanceOf('\GuzzleHttp\Psr7\Response', $response);
    }

    /**
     * Returns an unparsed response.
     *
     * @covers jamiehollern\eventbrite\Eventbrite::canConnect
     * @covers jamiehollern\eventbrite\Eventbrite::get
     * @covers jamiehollern\eventbrite\Eventbrite::call
     * @covers jamiehollern\eventbrite\Eventbrite::validMethod
     */
    public function testCanConnectTrue()
    {
        // Create a mock and queue a response.
        $mock = new MockHandler([
          new Response(200, ['Content-Type' => 'application/json'],
            '{ "test": "json" }'),
        ]);
        $handler = HandlerStack::create($mock);
        // Inject the mock handler into the config when instantiating.
        $eventbrite = new Eventbrite('valid_token', ['handler' => $handler]);
        $can_connect = $eventbrite->canConnect();
        $this->assertTrue($can_connect);
    }

    /**
     * Returns an unparsed response.
     *
     * @covers jamiehollern\eventbrite\Eventbrite::canConnect
     * @covers jamiehollern\eventbrite\Eventbrite::get
     * @covers jamiehollern\eventbrite\Eventbrite::call
     * @covers jamiehollern\eventbrite\Eventbrite::validMethod
     */
    public function testCanConnectFalse()
    {
        // Create a mock and queue a response.
        $mock = new MockHandler([
          new Response(500),
        ]);
        $handler = HandlerStack::create($mock);
        // Inject the mock handler into the config when instantiating.
        $eventbrite = new Eventbrite('valid_token', ['handler' => $handler]);
        $can_connect = $eventbrite->canConnect();
        $this->assertFalse($can_connect);
    }

    /**
     * @covers jamiehollern\eventbrite\Eventbrite::validMethod
     */
    public function testValidMethodTrue()
    {
        $method = 'PATCH';
        $valid = $this->eventbrite->validMethod($method);
        $this->assertTrue($valid);
    }

    /**
     * @covers jamiehollern\eventbrite\Eventbrite::validMethod
     */
    public function testValidMethodFalse()
    {
        $method = 'PUNT';
        $valid = $this->eventbrite->validMethod($method);
        $this->assertFalse($valid);
    }

    /**
     * @covers jamiehollern\eventbrite\Eventbrite::isValidJson
     */
    public function testValidJson()
    {
        $input = [];
        $valid = $this->eventbrite->isValidJson($input);
        $this->assertFalse($valid);
    }

}
