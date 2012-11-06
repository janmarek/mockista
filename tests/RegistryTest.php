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
		$mock1 = $this->object->createMock();
		$mock1->expects('abc')->twice();
		$this->object->assertExpectations();
	}

}
