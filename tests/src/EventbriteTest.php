<?php
/**
 * @file
 * Unit tests for the Eventbrite class.
 */
namespace jamiehollern\eventbrite\Tests;

use jamiehollern\eventbrite\Eventbrite;
use Mockery\Mock as m;

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
    public $eventbrite;

    public function setUp() {
        $this->eventbrite = new Eventbrite('token');
    }

    /**
     * @covers jamiehollern\eventbrite\Eventbrite::validMethod
     */
    public function testValidMethod() {
        $method = 'PATCH';
        $valid = $this->eventbrite->validMethod($method);
        $this->assertTrue($valid);
    }

}
