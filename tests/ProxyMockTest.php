<?php

namespace Mockista\Test;

use Mockista;
use Mockista\MockBuilder;
use Mockista\MethodInterface;

require_once __DIR__ . '/fixtures/exception.php';
require_once __DIR__ . '/fixtures/mockedObject.php';

class ProxyMockTest extends \PHPUnit_Framework_TestCase
{

	/** @var \Mockista\Mock */
	private $object;

	protected function setUp()
	{
		$mocked = new \MockedObject(100);
		$this->object = Mockista\mock($mocked);
	}

	public function testMethod()
	{
		$method = $this->object->expects('abc');
		$this->assertTrue($method instanceof MethodInterface);
	}

	public function testMethodPass()
	{
		$this->object->expects('getOne')->andPass();
		$this->object->expects('timesTwo')->andPass();
		$this->assertEquals(1, $this->object->getOne());
		$this->assertEquals(4, $this->object->timesTwo(2));
		$this->assertEquals(8, $this->object->timesTwo(4));
	}

	/**
	 * @expectedException Mockista\MockException
	 * @expectedExceptionCode 5
	 * @expectedExceptionMessage Cannot pass expected call to mocked object. Method 'getInvalid' in 'MockedObject' doesn't exists.
	 */
	public function testMethodPassInvalid()
	{
		$this->object->expects('getInvalid')->andPass();
		$this->object->getInvalid();
	}

	/**
	 * @expectedException Mockista\MockException
	 * @expectedExceptionCode 1
	 * @expectedExceptionMessage Expected method MockedObject::getOne() should never be called but called once.
	 */
	public function testCollectNever()
	{
		$this->object->expects('getOne')->andPass()->never();
		$this->object->getOne();
		$this->object->assertExpectations();
	}

	public function testCollectNeverAndNotCalled()
	{
		$this->object->expects('getOne')->andPass()->never();
		$this->object->assertExpectations();
	}

	public function testCollectExactly()
	{
		$this->object->expects('getOne')->andPass()->exactly(3);
		$this->object->getOne();
		$this->object->getOne();
		$this->object->getOne();
		$this->object->assertExpectations();
	}

	/**
	 * @expectedException Mockista\MockException
	 * @expectedExceptionCode 1
	 * @expectedExceptionMessage Expected method MockedObject::getOne() should be called exactly twice but not called at all.
	 */
	public function testCollectExactlyBad()
	{
		$this->object->expects('getOne')->andPass()->exactly(2);
		$this->object->assertExpectations();
	}

	public function testCollectAtLeast()
	{
		$this->object->expects('getOne')->andPass()->atLeast(2);
		$this->object->getOne();
		$this->object->getOne();
		$this->object->getOne();
		$this->object->assertExpectations();
	}

	/**
	 * @expectedException Mockista\MockException
	 * @expectedExceptionCode 2
	 * @expectedExceptionMessage Expected method MockedObject::getOne() should be called at least twice but not called at all.
	 */
	public function testCollectAtLeastBad()
	{
		$this->object->expects('getOne')->andPass()->atLeast(2);
		$this->object->assertExpectations();
	}

	public function testCollectNoMoreThan()
	{
		$this->object->expects('getOne')->andPass()->noMoreThan(3);
		$this->object->getOne();
		$this->object->getOne();
		$this->object->getOne();
		$this->object->assertExpectations();
	}

	/**
	 * @expectedException Mockista\MockException
	 * @expectedExceptionCode 3
	 * @expectedExceptionMessage Expected method MockedObject::getOne() should be called no more than once but called twice.
	 */
	public function testCollectNoMoreThanOnceAttribute()
	{
		$this->object->expects('getOne')->andPass()->noMoreThanOnce;
		$this->object->getOne();
		$this->object->getOne();
		$this->object->assertExpectations();
	}

	/**
	 * @expectedException Mockista\MockException
	 * @expectedExceptionCode 3
	 * @expectedExceptionMessage Expected method MockedObject::getOne() should be called no more than twice but called 3 times.
	 */
	public function testCollectNoMoreThanBad()
	{
		$this->object->expects('getOne')->andPass()->noMoreThan(2);
		$this->object->getOne();
		$this->object->getOne();
		$this->object->getOne();
		$this->object->assertExpectations();
	}

	public function testMultipleCalls()
	{
		$this->object->expects('timesTwo')->with(1)->andPass();
		$this->object->expects('timesTwo')->with(2)->andPass();
		$this->object->expects('timesTwo')->andPass();

		$this->assertEquals(2, $this->object->timesTwo(1));
		$this->assertEquals(4, $this->object->timesTwo(2));
		$this->assertEquals(10, $this->object->timesTwo(5));
		$this->assertEquals(12, $this->object->timesTwo(6));
	}

	public function testNotDefinedArgs()
	{
		$mocked = new \MockedObject(100);
		$builder = new MockBuilder($mocked);
		$builder->timesTwo()->andPass();
		$builder->timesTwo()->with(array())->andReturn(2);
		$mock = $builder->getMock();

		$this->assertEquals(10, $mock->timesTwo(5));
		$this->assertEquals(2, $mock->timesTwo(array()));
	}

	public function testState()
	{
		$this->assertEquals(100, $this->object->getState());
		$this->object->setState(101);
		$this->assertEquals(101, $this->object->getState());
	}

	public function testPassUnexpectedMethod()
	{
		$this->assertEquals(4, $this->object->timesTwo(2));
		$this->assertEquals(8, $this->object->timesTwo(4));
	}
	
	/**
	 * @expectedException Mockista\MockException
	 * @expectedExceptionCode 5
	 * @expectedExceptionMessage Cannot pass unexpected call to mocked object. Method 'getInvalid' in 'MockedObject' doesn't exists.
	 */
	public function testPassUnexpectedMethodInvalid()
	{
		$this->object->getInvalid();
	}


}
