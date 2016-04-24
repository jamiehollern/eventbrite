<?php
/**
 * @file
 * Unit tests for the Eventbrite class.
 */
namespace JamieHollern\Eventbrite\Tests;

use JamieHollern\Eventbrite\Eventbrite;
use Mockery\Mock as m;

/**
 * Class EventbriteTest
 *
 * @package JamieHollern\Eventbrite\Tests
 */
class EventbriteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \JamieHollern\Eventbrite\Eventbrite
     */
    public $eventbrite;

    public function setUp() {
        $this->eventbrite = new Eventbrite('token');
    }

    /**
     * @covers JamieHollern\Eventbrite\Eventbrite::validMethod
     */
    public function testValidMethod() {
        $method = 'PATCH';
        $valid = $this->eventbrite->validMethod($method);
        $this->assertTrue($valid);
    }

}
