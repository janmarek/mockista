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
		$mock->d(11)->andReturn(true);
		$mock->freeze();

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


