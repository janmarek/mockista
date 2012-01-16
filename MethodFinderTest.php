<?php

use Mockista\MethodInterface;

require_once __DIR__ . "/bootstrap.php";


class MethodFinderTest_Dummy1234
{
	function a()
	{
	}

	static function b(Array $c = array('a'))
	{
	}
}

class MethodFinderTest extends KDev_Test
{
	function prepare()
	{
		$this->object = new Mockista\MethodFinder;
	}

	function testMethodA()
	{
		$methods = $this->object->methods("MethodFinderTest_Dummy1234");
		$this->assertTrue(array_key_exists("a", $methods));
		$this->assertTrue(array_key_exists("parameters", $methods['a']));
		$this->assertEquals(0, sizeof($methods['a']['parameters']));
		$this->assertFalse($methods['a']['static']);
	}

	function testMethodB()
	{
		$methods = $this->object->methods("MethodFinderTest_Dummy1234");
		$this->assertEquals(1, sizeof($methods['b']['parameters']));
		$this->assertTrue($methods['b']['static']);
	}
}
