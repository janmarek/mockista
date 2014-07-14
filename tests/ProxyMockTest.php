<?php

namespace Mockista\Test;

use Mockista\Mock;

require __DIR__ . '/fixtures/proxyMock.php';

/**
 * @author Jan Marek
 */
class ProxyMockTest extends \PHPUnit_Framework_TestCase
{

	/** @var Mock */
	private $object;

	protected function setUp()
	{
		$this->object = new Mock('Mockista\Test\ProxiedObject');
	}

	public function testDefaultConstructorArgs()
	{
		$this->object->expects('getConstructorArgs')->andCallOriginalMethod();
		$this->assertEquals(array(), $this->object->getConstructorArgs());
		$this->object->assertExpectations();
	}

	public function testSetConstructorArgs()
	{
		$this->object->callConstructorWithArgs(array(1, 2, 3));
		$this->object->expects('getConstructorArgs')->andCallOriginalMethod();
		$this->assertEquals(array(1, 2, 3), $this->object->getConstructorArgs());
		$this->object->assertExpectations();
	}

	/**
	 * @expectedException Mockista\InvalidStateException
	 */
	public function testSetConstructorArgsTwice()
	{
		$this->object->callConstructorWithArgs(array(1, 2, 3));
		$this->object->callConstructorWithArgs(array(3, 4, 5));
	}

	/**
	 * @expectedException Mockista\InvalidStateException
	 */
	public function testSetConstructorArgsTwice2()
	{
		$this->object->expects('getConstructorArgs')->andCallOriginalMethod();
		$this->assertEquals(array(), $this->object->getConstructorArgs());
		$this->object->callConstructorWithArgs(array(1, 2, 3));
	}

	public function testMethodWithParams()
	{
		$this->object->expects('add')->andCallOriginalMethod();
		$this->assertEquals(3, $this->object->add(1, 2));
		$this->object->assertExpectations();
	}

	public function testCombineWithCountAssertion()
	{
		$this->object->expects('add')->once()->andCallOriginalMethod();
		$this->assertEquals(3, $this->object->add(1, 2));
		$this->object->assertExpectations();
	}

	/**
	 * @expectedException Mockista\MockException
	 */
	public function testCombineWithCountAssertionError()
	{
		$this->object->expects('add')->exactly(3)->andCallOriginalMethod();
		$this->assertEquals(3, $this->object->add(1, 2));
		$this->object->assertExpectations();
	}

	public function testCallOtherMethod()
	{
		$this->markTestSkipped('Not implemented yet.');

		$this->object->expects('addToTwo')->andCallOriginalMethod();
		$this->object->expects('getTwo')->andReturn(3);

		$this->assertEquals(5, $this->object->addToTwo(2));
		$this->object->assertExpectations();
	}

}
