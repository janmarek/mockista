<?php
namespace Mockista\Test;

use Mockista;

class RegistryTest extends \PHPUnit_Framework_TestCase
{

	/** @var \Mockista\Registry */
	private $object;

	function setUp()
	{
		$this->object = new Mockista\Registry();
	}

	/**
	 * @expectedException Mockista\MockException
	 */
	function testAssertExpectations()
	{
		$mock1 = $this->object->create();
		$mock1->expects('abc')->twice();
		$this->object->assertExpectations();
	}

	function testMocksAreNamedProperly()
	{
		$this->assertEquals('#1', $this->object->create()->getName());
		$this->assertEquals('service', $this->object->createNamed('service')->getName());
		$this->assertEquals('stdClass#3', $this->object->createBuilder('stdClass')->getMock()->getName());
		$this->assertEquals('#4', $this->object->create()->getName());
		$this->assertEquals('Mockista\A#5', $this->object->create('Mockista\A')->getName());
		$this->assertEquals('abc', $this->object->createNamedBuilder('abc', 'Mockista\A')->getMock()->getName());
	}

	/**
	 * @expectedException Mockista\InvalidArgumentException
	 */
	function testDupliciteNamesWhenUsingNamedMocksThrowsException()
	{
		$mock1 = $this->object->createNamed('service');
		$mock1 = $this->object->createNamed('service');
	}

	/**
	 * @expectedException Mockista\InvalidArgumentException
	 */
	function testDupliciteNamesWhenUsingNamedBuildersThrowsException()
	{
		$mock1 = $this->object->createNamedBuilder('service');
		$mock1 = $this->object->createNamedBuilder('service');
	}

	function testGetMockByName()
	{
		$this->object->createNamed('first');
		$mock = $this->object->getMockByName('first');
		$this->assertInstanceOf('Mockista\\Mock', $mock);
		$this->assertEquals('first', $mock->getName());
	}

	/**
	 * @expectedException Mockista\InvalidArgumentException
	 */
	function testGetNotExistingMock()
	{
		$this->object->getMockByName('none');
	}

	function testGetBuilderByName()
	{
		$mock1 = $this->object->createNamed('first');
		$builder = $this->object->getBuilderByName('first');
		$this->assertInstanceOf('Mockista\\MockBuilder', $builder);
		$this->assertSame($mock1, $builder->getMock());
		$this->assertSame($mock1, $this->object->getMockByName('first'));
	}

	function testFreezeMock()
	{
		$mock = $this->object->create("Mockista\A");
		$mock->setName("Test");
		$this->assertEquals("Test", $mock->getName());

		$mock->expects("setName")->andReturn("Already freezed");
		$mock->freeze();
		$this->assertEquals("Already freezed", $mock->setName("name"));
	}

	/**
	 * @expectedException Mockista\MockException
	 * @expectedMessage   Unexpected call in mock Test::getName()
	 */
	function testFreezeMockExceptionOnUndefinedMethod()
	{
		$mock = $this->object->create("Mockista\A");
		$mock->freeze();
		$this->assertEquals("Test", $mock->getName());
	}

}
