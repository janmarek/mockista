<?php

use Mockista\MethodInterface;

require_once __DIR__ . "/bootstrap.php";

interface MethodFinderTest_Dummy1234If2
{
}

interface MethodFinderTest_Dummy1234If1
{
}

class MethodFinderTest_Dummy1234Parent implements MethodFinderTest_Dummy1234If1
{
	function ab()
	{
	}
}

class MethodFinderTest_Dummy1234 extends MethodFinderTest_Dummy1234Parent implements MethodFinderTest_Dummy1234If2
{
	static function b(Array $c = array('a'))
	{
	}

	function c(Exception $d = null)
	{
	}
}

class MethodFinderTest extends KDev_Test
{
	function prepare()
	{
		$this->object = new Mockista\MethodFinder;
	}


	function testMethodAStaticNotStatic()
	{
		$methods = $this->object->methods("MethodFinderTest_Dummy1234");
		$this->assertTrue(array_key_exists("ab", $methods));
		$this->assertTrue(array_key_exists("parameters", $methods['ab']));
		$this->assertEquals(0, sizeof($methods['ab']['parameters']));
		$this->assertFalse($methods['ab']['static']);
	}

	function testMethodBNumberParams()
	{
		$methods = $this->object->methods("MethodFinderTest_Dummy1234");
		$this->assertEquals(1, sizeof($methods['b']['parameters']));
		$this->assertTrue($methods['b']['static']);
	}

	function testDefaultParam()
	{
		$methods = $this->object->methods("MethodFinderTest_Dummy1234");
		$this->assertEquals(array("a"), $methods['b']['parameters'][0]['default']);
		$this->assertEquals('Array', $methods['b']['parameters'][0]['typehint']);

		$this->assertEquals('Exception', $methods['c']['parameters'][0]['typehint']);
	}
}
