<?php

namespace Mockista\Test;

require __DIR__ . '/fixtures/factory.php';

class MockFactoryTest extends \PHPUnit_Framework_TestCase
{

	function testGenerateClass()
	{
		$obj = \Mockista\mock("Mockista\\MockFactoryTest_Generated");
		$this->assertTrue($obj instanceof \Mockista\MockFactoryTest_Generated);
	}

	function mockMethods()
	{
		$obj = \Mockista\mock("Mockista\\MockFactoryTest_Generated", array(
			'x' => 1,
			'y' => function () {
				return 2;
			}
		));

		return $obj;
	}

	function testGenerateClassArgsMethods()
	{
		$obj = $this->mockMethods();
		$this->assertTrue($obj instanceof \Mockista\MockFactoryTest_Generated);
		$this->assertEquals(1, $obj->x);
		$this->assertEquals(2, $obj->y());
	}

}

