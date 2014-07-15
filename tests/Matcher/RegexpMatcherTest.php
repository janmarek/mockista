<?php

namespace Mockista\Test\Matcher;

use Mockista\Matcher\Matchers;
use Mockista\Matcher\RegexpMatcher;

class MatchersTest extends \PHPUnit_Framework_TestCase
{

	public function testMatch()
	{
		$this->assertTrue(Matchers::isArray()->match(array(1, 2, 3)));
	}

	public function testNotMatch()
	{
		$matcher = new RegexpMatcher('/[0-9]/');
		$this->assertFalse($matcher->match('lorem ipsum'));
	}

}
