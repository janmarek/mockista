<?php

use Mockista\MethodInterface;

require_once __DIR__ . "/bootstrap.php";

class CleverAssertsTest extends KDev_Test
{
	function testIExpect()
	{
		$this->assertTrue(Mockista\I_expect() instanceof Mockista\Expectation);
	}

	private function willThrow($closure)
	{
		try {
			$closure();
			$this->fail("didn't throw");
		} catch (PHPUnit_Framework_AssertionFailedError $e) {
			$this->assertTrue(true);
		}
	}

	function testIsTrue()
	{
		$this->willThrow(function() {
			Mockista\I_expect(true)->isTrue;
		});
	}

	function testIsFalse()
	{
		$this->willThrow(function() {
			Mockista\I_expect(false)->isFalse;
		});
	}

	function testIsEqualTo()
	{
		$this->willThrow(function() {
			Mockista\I_expect("abc")->isEqualTo("ddd");
		});		
	}
}

