<?php

namespace Mockista\Test\Matcher;

use Mockista\Matcher\LinearArrayMatcher;
use Mockista\Matcher\Matchers;
use Mockista\Test\Circular;

require_once __DIR__ . '/../fixtures/circular.php';

class LinearArrayMatcherTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @dataProvider matchProvider
	 */
	public function testMatch($value1, $value2)
	{
		$matcher = new LinearArrayMatcher($value1);
		$this->assertTrue($matcher->match($value2));
	}

	/**
	 * @dataProvider notMatchProvider
	 */
	public function testNotMatch($value1, $value2)
	{
		$matcher = new LinearArrayMatcher($value1);
		$this->assertFalse($matcher->match($value2));
	}

	public function matchProvider()
	{
		$object = new Circular();
		return array(
			array(array(), array()),
			array(array(1, 2, NULL), array(1, 2, NULL)),
			array(array(Matchers::isInt()), array(1)),
			array(array(Matchers::isInt(), 3), array(1, 3)),
			array(array($object, Matchers::isString()), array($object, 'lorem')),
		);
	}

	public function notMatchProvider()
	{
		$object = new Circular();
		$object2 = new Circular();

		return array(
			array(array(), array(1)),
			array(array(Matchers::isInt()), array('lorem')),
			array(array($object, Matchers::isString()), array($object2, 'lorem')),
		);
	}

}
