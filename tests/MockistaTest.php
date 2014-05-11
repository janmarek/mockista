<?php

namespace Mockista\Test;

use Mockista;
use Mockista\MockBuilder;
use Mockista\MethodInterface;

require __DIR__ . '/fixtures/exception.php';
require __DIR__ . '/fixtures/circular.php';

/**
 * @author Jiri Knesl
 */
class MockistaTest extends \PHPUnit_Framework_TestCase
{

	/** @var \Mockista\Mock */
	private $object;

	protected function setUp()
	{
		$this->object = Mockista\mock();
	}

	public function testAttribute()
	{
		$this->object->abc = 11;
		$this->assertEquals(11, $this->object->abc);
	}

	public function testMethod()
	{
		$method = $this->object->expects('abc');
		$this->assertTrue($method instanceof MethodInterface);
	}

	public function testWithAny()
	{
		$method = $this->object->expects('abc')->with(1, 2)->withAny();
		$this->object->abc('any');
		$this->object->abc(4, 5);
		$this->object->abc();
		$this->object->assertExpectations();
	}

	public function testMethodReturn()
	{
		$this->object->expects('abc')->andReturn(11);
		$this->assertEquals(11, $this->object->abc());
	}

	public function testMethodReturnMultiple()
	{
		$this->object->expects('abc')->andReturn(1, 2, 3);
		$this->assertEquals(1, $this->object->abc());
		$this->assertEquals(2, $this->object->abc());
		$this->assertEquals(3, $this->object->abc());
		$this->assertEquals(3, $this->object->abc());
		$this->assertEquals(3, $this->object->abc());
	}

	public function testMethodCallback()
	{
		$this->object->expects('abc')->with('aaa')->andCallback(function ($name) {
			return strtoupper($name);
		});
		$this->assertEquals('AAA', $this->object->abc('aaa'));
	}

	/**
	 * @expectedException MockistaTestException
	 */
	public function testMethodThrow()
	{
		$this->object->expects('abc')->andThrow(new \MockistaTestException);
		$this->object->abc();
	}

	/**
	 * @expectedException Mockista\MockException
	 * @expectedExceptionCode 1
	 * @expectedExceptionMessage Expected method unnamed::abc() should never be called but called once.
	 */
	public function testCollectNever()
	{
		$this->object->expects('abc')->never();
		$this->object->abc();
		$this->object->assertExpectations();
	}

	public function testCollectNeverAndNotCalled()
	{
		$this->object->expects('abc')->never();
		$this->object->assertExpectations();
	}

	public function testCollectExactly()
	{
		$this->object->expects('abc')->exactly(3);
		$this->object->abc();
		$this->object->abc();
		$this->object->abc();
		$this->object->assertExpectations();
	}

	/**
	 * @expectedException Mockista\MockException
	 * @expectedExceptionCode 1
	 * @expectedExceptionMessage Expected method unnamed::abc() should be called exactly twice but not called at all.
	 */
	public function testCollectExactlyBad()
	{
		$this->object->expects('abc')->exactly(2);
		$this->object->assertExpectations();
	}

	public function testCollectAtLeast()
	{
		$this->object->expects('abc')->atLeast(2);
		$this->object->abc();
		$this->object->abc();
		$this->object->abc();
		$this->object->assertExpectations();
	}

	/**
	 * @expectedException Mockista\MockException
	 * @expectedExceptionCode 2
	 * @expectedExceptionMessage Expected method unnamed::abc() should be called at least twice but not called at all.
	 */
	public function testCollectAtLeastBad()
	{
		$this->object->expects('abc')->atLeast(2);
		$this->object->assertExpectations();
	}

	public function testCollectNoMoreThan()
	{
		$this->object->expects('abc')->noMoreThan(3);
		$this->object->abc();
		$this->object->abc();
		$this->object->abc();
		$this->object->assertExpectations();
	}

	/**
	 * @expectedException Mockista\MockException
	 * @expectedExceptionCode 3
	 * @expectedExceptionMessage Expected method unnamed::abc() should be called no more than once but called twice.
	 */
	public function testCollectNoMoreThanOnceAttribute()
	{
		$this->object->expects('abc')->noMoreThanOnce;
		$this->object->abc();
		$this->object->abc();
		$this->object->assertExpectations();
	}

	/**
	 * @expectedException Mockista\MockException
	 * @expectedExceptionCode 3
	 * @expectedExceptionMessage Expected method unnamed::abc() should be called no more than twice but called 3 times.
	 */
	public function testCollectNoMoreThanBad()
	{
		$this->object->expects('abc')->noMoreThan(2);
		$this->object->abc();
		$this->object->abc();
		$this->object->abc();
		$this->object->assertExpectations();
	}

	public function testMultipleCalls()
	{
		$this->object->expects('abc')->with(1)->andReturn(2);
		$this->object->expects('abc')->with(2)->andReturn(3);
		$this->object->expects('abc')->andReturn(4);

		$this->assertEquals(2, $this->object->abc(1));
		$this->assertEquals(3, $this->object->abc(2));
		$this->assertEquals(4, $this->object->abc());
		$this->assertEquals(4, $this->object->abc('aa'));
	}

	public function testMockArgs()
	{
		$mock = Mockista\mock(array(
			"x" => 11,
			"y" => function ($a) {
				return $a * 2;
			}
		));
		$this->assertEquals(11, $mock->x());
		$this->assertEquals(4, $mock->y(2));
	}

	public function testNotDefinedArgs()
	{
		$builder = new MockBuilder();
		$builder->abc()->andReturn(1);
		$builder->abc()->with(array())->andReturn(2);
		$mock = $builder->getMock();

		$this->assertEquals(1, $mock->abc());
		$this->assertEquals(2, $mock->abc(array()));
	}

	public function testCircularParameter()
	{
		$arg = array('circular' => new Circular());

		$mock = Mockista\mock();
		$mock->expects('method')->with($arg)->once()->andReturn(1);

		$this->assertEquals(1, $mock->method($arg));
	}

	/**
	 * @expectedException Mockista\MockException
	 * @expectedExceptionCode 4
	 */
	public function testNotMatchedCircularParameter()
	{
		$arg = new Circular();

		$mock = Mockista\mock();
		$mock->expects('method')->with()->once()->andReturn(1);

		$this->assertEquals(1, $mock->method($arg, 4, 'asdf'));
	}

}
