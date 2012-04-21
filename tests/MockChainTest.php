
<?php

use Mockista\MethodInterface;

require_once dirname(__DIR__) . "/bootstrap.php";


class MockChainTest extends KDev_Test
{
	function prepare()
	{
		$this->object = new Mockista\MockChain;
	}

	function mockD()
	{
		$mock = Mockista\mock();
		$mock->d(11)->andReturn(true);
		return $mock;
	}

	function testAddLastCalledMethod()
	{
		$this->object->addLastCalledMethod("d", $this->mockD);
		$ret = $this->object->a->b()->c("abc")->d(11);
		$this->assertTrue($ret);
	}
}


