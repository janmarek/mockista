<?php

namespace Mockista\Test\Matcher;

use Mockista\Matcher\ValueMatcher;
use Mockista\Test\Circular;

require_once __DIR__ . '/../fixtures/circular.php';

class ValueMatcherTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @dataProvider matchProvider
	 */
	public function testMatch($value1, $value2)
	{
		$matcher = new ValueMatcher($value1);
		$this->assertTrue($matcher->match($value2));
	}

	/**
	 * @dataProvider notMatchProvider
	 */
	public function testNotMatch($value1, $value2)
	{
		$matcher = new ValueMatcher($value1);
		$this->assertFalse($matcher->match($value2));
	}

	public function matchProvider()
	{
		$object = new Circular();
		return array(
			array(1, 1),
			array(1.0, 1.0),
			array(NULL, NULL),
			array('lorem', 'lorem'),
			array(array(1, 2, NULL), array(1, 2, NULL)),
			array($object, $object),
		);
	}

	public function notMatchProvider()
	{
		$object = new Circular();
		$object2 = new Circular();
		return array(
			array(1, 'lorem'),
			array(1.0, 1),
			array(NULL, FALSE),
			array('lorem', 'ipsum'),
			array(array(1, 2, 'dolor'), array(1, 2, NULL)),
			array($object, $object2),
		);
	}

}
