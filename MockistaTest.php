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


	public function testMethodCallback()
	{
		$this->object->abc('aaa')->andCallback(function($name){return strtoupper($name);});
		$this->object->freeze();
		$this->assertEquals('AAA', $this->object->abc('aaa'));
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

	/**
	 * @expectedException Exception
	 * @expectedExceptionCode 1
	 */
	public function testCollectNever()
	{
		$this->object->abc()->never();
		$this->object->freeze()->abc();
		$this->object->collect();
	}

	public function testCollectExactly()
	{
		$this->object->abc()->exactly(3);
		$this->object->freeze()->abc();
		$this->object->abc();
		$this->object->abc();
		$this->object->collect();
	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionCode 1
	 */
	public function testCollectExactlyBad()
	{
		$this->object->abc()->exactly(2);
		$this->object->freeze()->collect();
	}

	public function testCollectAtLeast()
	{
		$this->object->abc()->atLeast(2);
		$this->object->freeze()->abc();
		$this->object->abc();
		$this->object->abc();
		$this->object->collect();
	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionCode 2
	 */
	public function testCollectAtLeastBad()
	{
		$this->object->abc()->atLeast(2);
		$this->object->freeze()->collect();
	}

	public function testCollectNoMoreThan()
	{
		$this->object->abc()->noMoreThan(3);
		$this->object->freeze()->abc();
		$this->object->abc();
		$this->object->abc();
		$this->object->collect();
	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionCode 3
	 */
	public function testCollectNoMoreThanBad()
	{
		$this->object->abc()->noMoreThan(2);
		$this->object->freeze()->abc();
		$this->object->abc();
		$this->object->abc();
		$this->object->collect();
	}

}
