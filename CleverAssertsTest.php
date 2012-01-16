<?php

use Mockista\MethodInterface;

require_once __DIR__ . "/bootstrap.php";

class CleverAssertsTest extends KDev_Test
{
	function testIExpect()
	{
		$this->assertTrue(Mockista\I_expect() instanceof Mockista\Expectation);
	}
}

