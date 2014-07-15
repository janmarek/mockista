<?php

namespace Mockista\Test\Matcher;

use Mockista\Matcher\Matchers;

class RegexpMatcherTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @dataProvider matchProvider
	 */
	public function testMatch($matcher, $value)
	{
		$this->assertTrue($matcher->match($value));
	}

	public function matchProvider()
	{
		return array(
			array(Matchers::isBool(), FALSE),
			array(Matchers::isNumeric(), '123'),
			array(Matchers::isInt(), 1),
			array(Matchers::isFloat(), 1.0),
			array(Matchers::isArray(), array(1, 2, 3)),
			array(Matchers::isString(), 'lorem'),
			array(Matchers::regexp('/lorem/'), 'lorem ipsum'),
		);
	}

}
