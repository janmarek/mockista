<?php

namespace Mockista\Test;

use Mockista;
use Mockista\MethodInterface;

require __DIR__ . '/fixtures/methodFinder.php';

class MethodFinderTest extends \PHPUnit_Framework_TestCase
{
	function setUp()
	{
		$this->object = new Mockista\MethodFinder;
	}

	function testMethodAStaticNotStatic()
	{
		$methods = $this->object->methods("MethodFinderTest_Dummy1234");
		$this->assertTrue(array_key_exists("ab", $methods));
		$this->assertTrue(array_key_exists("parameters", $methods['ab']));
		$this->assertEquals(0, sizeof($methods['ab']['parameters']));
		$this->assertEquals(true, $methods['ab']['final']);
		$this->assertFalse($methods['ab']['static']);
	}

	function testMethodBNumberParams()
	{
		$methods = $this->object->methods("MethodFinderTest_Dummy1234");
		$this->assertEquals(1, sizeof($methods['b']['parameters']));
		$this->assertTrue($methods['b']['static']);
		$this->assertFalse($methods['b']['passedByReference']);
	}

	function testDefaultParam()
	{
		$methods = $this->object->methods("MethodFinderTest_Dummy1234");
		$this->assertEquals(array("a"), $methods['b']['parameters'][0]['default']);
		$this->assertEquals('Array', $methods['b']['parameters'][0]['typehint']);

		$this->assertEquals('Exception', $methods['c']['parameters'][0]['typehint']);
		$this->assertEquals(true, $methods['c']['parameters'][0]['passedByReference']);
		$this->assertTrue($methods['c']['passedByReference']);
	}
}
