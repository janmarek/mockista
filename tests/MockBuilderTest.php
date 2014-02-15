<?php

namespace Mockista\Test;

use Mockista\MockBuilder;

require __DIR__ . '/fixtures/factory.php';

class MockBuilderTest extends \PHPUnit_Framework_TestCase
{

	function testAddMethods()
	{
		$builder = new MockBuilder();
		$builder->addMethods(array(
			'x' => 1,
			'y' => function ($a) {
				return $a * 2;
			}
		));

		$mock = $builder->getMock();

		$this->assertTrue($mock instanceof \Mockista\Mock);
		$this->assertEquals(1, $mock->x());
		$this->assertEquals(4, $mock->y(2));
	}

	function testGenerateClass()
	{
		$builder = new MockBuilder('Mockista\MockFactoryTest_Generated');
		$mock = $builder->getMock();
		$this->assertInstanceOf('Mockista\MockFactoryTest_Generated', $mock);
	}

	function testBuildingFromExistingMock() {
		$builder = new MockBuilder('Mockista\MockFactoryTest_Generated');
		$mock = $builder->getMock();
		$builder2 = new MockBuilder($mock);
		$mock2 = $builder2->getMock();
		$this->assertSame($mock, $mock2);
	}

	function testFunctionShortcut()
	{
		$this->assertInstanceOf('Mockista\MockFactoryTest_Generated', \Mockista\mock('Mockista\MockFactoryTest_Generated'));
	}

	function testGenerateClassArgsMethods()
	{
		$mock = \Mockista\mock('Mockista\MockFactoryTest_Generated', array(
			'x' => 1,
			'y' => function () {
				return 2;
			}
		));

		$this->assertInstanceOf('\Mockista\MockFactoryTest_Generated', $mock);
		$this->assertEquals(1, $mock->x(1, 2, 3));
		$this->assertEquals(2, $mock->y());
	}

	function testMagicCallMethodRegistering()
	{
		$builder = new MockBuilder();
		$builder->abc(1)->andReturn(2);
		$builder->abc(3)->andReturn(4);
		$this->assertEquals(2, $builder->getMock()->abc(1));
	}

	function testMockedClassHasName() {
		$builder = new MockBuilder('Mockista\A');
		$mock = $builder->getMock();
		$this->assertEquals('Mockista\A', $mock->mockista->mockName);
	}

	function testMockedMethodHasOwningMockAndName() {
		$builder = new MockBuilder('Mockista\A');
		$method = $builder->mockme();
		$mock = $builder->getMock();
		$this->assertEquals($mock->mockista, $method->owningMock);
		$this->assertEquals('mockme', $method->name);
	}

}

