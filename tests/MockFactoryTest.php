<?php

namespace Mockista\Test;

use Mockista\MethodInterface;

class MockFactoryTest_Generated
{
	function y()
	{
		return 3;
	}

	function z()
	{
		return 4;
	}
}

class MockFactoryTest extends \KDev_Test
{
	function testGenerateClass()
	{
		$obj = \Mockista\mock("Mockista\\Test\\MockFactoryTest_Generated");
		$this->assertTrue($obj instanceof MockFactoryTest_Generated);
	}

	function mockMethods()
	{
		$obj = \Mockista\mock("Mockista\\Test\\MockFactoryTest_Generated", array('x'=>1, 'y'=>function(){return 2;}));
		return $obj;
	}

	function testGenerateClassArgsMethods()
	{
		$obj = $this->mockMethods;
		$this->assertTrue($obj instanceof MockFactoryTest_Generated);
		$this->assertEquals(1, $obj->x);
		$this->assertEquals(2, $obj->y());
	}
}

