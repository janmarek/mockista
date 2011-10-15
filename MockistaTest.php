<?php

use Mockista\MethodInterface;

require_once __DIR__ . "/bootstrap.php";

class MockistaTestException extends Exception
{
}

// require_once "PHPUnit/Framework.php";

/**
 * undocumented class
 *
 * @author Jiri Knesl
**/
class MockistaTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var Mockista
	 */
	private $object;

	/**
	 * 
	 */
	protected function setUp()
	{
		$this->object = Mockista\mock();
	}

	/**
	 * 
	 */
	public function testAttribute()
	{
		$this->object->abc = 11;
		$this->assertEquals(11, $this->object->abc);
	}

	public function testMethod()
	{
		$method = $this->object->abc();
		$this->assertTrue($method instanceof MethodInterface);
	}

	public function testMethodReturn()
	{
		$this->object->abc()->andReturn(11);
		$this->object->freeze();
		$this->assertEquals(11, $this->object->abc());
	}

	/**
	 * @expectedException MockistaTestException
	 */
	public function testMethodThrow()
	{
		$this->object->abc()->andThrow(new MockistaTestException);
		$this->object->freeze();
		$this->object->abc();
	}

}
