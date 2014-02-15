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

	function test_mocks_are_named_properly() {
		$this->assertEquals('#1', $this->object->create()->getMockName());
		$this->assertEquals('service', $this->object->createNamed('service')->getMockName());
		$this->assertEquals('stdClass#3', $this->object->createBuilder('stdClass')->getMock()->getMockName());
		$this->assertEquals('#4', $this->object->create()->getMockName());
		$this->assertEquals('Mockista\A#5', $this->object->create('Mockista\A')->getMockName());
		$this->assertEquals('abc', $this->object->createNamedBuilder('abc', 'Mockista\A')->getMock()->getMockName());
	}

	/**
	 * @expectedException Mockista\MockException
	 */
	function test_duplicite_names_when_using_named_mocks_throws_exception() {
		$mock1 = $this->object->createNamed('service');
		$mock1 = $this->object->createNamed('service');
	}

	/**
	 * @expectedException Mockista\MockException
	 */
	function test_duplicite_names_when_using_named_builders_throws_exception() {
		$mock1 = $this->object->createNamedBuilder('service');
		$mock1 = $this->object->createNamedBuilder('service');
	}

	function test_getMock() {
		$this->object->createNamed('first');
		$mock = $this->object->getMock('first');
		$this->assertInstanceOf('Mockista\\Mock', $mock);
		$this->assertEquals('first', $mock->getMockName());
	}

	/**
	 * @expectedException Mockista\MockException
	 */
	function test_getNotExistingMock() {
		$this->object->getMock('none');
	}

	function test_getBuilder() {
		$mock1 = $this->object->createNamed('first');
		$builder = $this->object->getBuilder('first');
		$this->assertInstanceOf('Mockista\\MockBuilder', $builder);
		$this->assertSame($mock1, $builder->getMock());
		$this->assertSame($mock1, $this->object->getMock('first'));
	}

}
