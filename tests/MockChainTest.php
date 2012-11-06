<?php

namespace Mockista\Test;

use Mockista;

class MockChainTest extends \PHPUnit_Framework_TestCase
{

	function setUp()
	{
		$this->object = new Mockista\MockChain;
	}

	function mockD()
	{
		$mock = Mockista\mock();
		$mock->expects('d', array(11))->andReturn(true);

		return $mock;
	}

	function testAddLastCalledMethod()
	{
		$mock = $this->mockD();
		$this->object->addLastCalledMethod("d", $mock);
		$ret = $this->object->a->b()->c("abc")->d(11);
		$this->assertTrue($ret);
		$mock->assertExpectations();
	}

}


