<?php

namespace Mockista\Test\ClassGenerator;

use Mockista;
use Mockista\ClassGenerator\MethodFinder;
use Mockista\ClassGenerator\ClassGenerator;

require __DIR__ . '/../fixtures/classGenerator.php';

class ClassGeneratorTest extends \PHPUnit_Framework_TestCase
{

	private $object;

	function setUp()
	{
		$this->object = new ClassGenerator();
	}

	function mockNoMethods()
	{
		$mock = Mockista\mock();
		$mock->expects('methods')->once->andReturn(array());

		return $mock;
	}

	function testEmptyClass()
	{
		$emptyClass = 'class ClassGeneratorTest_Empty_Generated extends ClassGeneratorTest_Empty
{
	public $mockista;

	function __construct()
	{
	}

	function __call($name, $args)
	{
		$l = call_user_func_array(array($this->mockista, $name), $args);
		return $l;
	}
}
';
		$this->object->setMethodFinder($this->mockNoMethods());
		$this->assertEquals($emptyClass, $this->object->generate("ClassGeneratorTest_Empty", "ClassGeneratorTest_Empty_Generated"));
	}

	function testNameSpace()
	{
		$emptyClassWithNamespace = 'use A\B\ClassGeneratorTest_Empty;

class A_B_ClassGeneratorTest_Empty_Generated implements ClassGeneratorTest_Empty
{
	public $mockista;

	function __construct()
	{
	}

	function __call($name, $args)
	{
		$l = call_user_func_array(array($this->mockista, $name), $args);
		return $l;
	}
}
';
		$this->object->setMethodFinder($this->mockNoMethods());
		$this->assertEquals($emptyClassWithNamespace, $this->object->generate("A\\B\\ClassGeneratorTest_Empty", "A_B_ClassGeneratorTest_Empty_Generated"));
	}

	function testMethod()
	{
		$classIncludingMethod = 'class ClassGeneratorTest_Method_Generated extends ClassGeneratorTest_Method
{
	public $mockista;

	function __construct()
	{
	}

	function __call($name, $args)
	{
		$l = call_user_func_array(array($this->mockista, $name), $args);
		return $l;
	}

	function &abc(&$a, $def = 123, $ghi = \'a\')
	{
		$l = call_user_func_array(array($this->mockista, \'abc\'), func_get_args());
		return $l;
	}

	function __destruct()
	{
	}
}
';
		$this->object->setMethodFinder(new MethodFinder());
		$this->assertEquals($classIncludingMethod, $this->object->generate("ClassGeneratorTest_Method", "ClassGeneratorTest_Method_Generated"));
	}

	function testInterface()
	{
		$interfaceBasedClass = 'class ClassGeneratorTest_Interface_Generated implements ClassGeneratorTest_Interface
{
	public $mockista;

	function __construct()
	{
	}

	function __call($name, $args)
	{
		$l = call_user_func_array(array($this->mockista, $name), $args);
		return $l;
	}

	function ai(Array $ax = array (  0 => 1,  1 => 2,  2 => 3,))
	{
		$l = call_user_func_array(array($this->mockista, \'ai\'), func_get_args());
		return $l;
	}
}
';
		$this->object->setMethodFinder(new MethodFinder());
		$this->assertEquals($interfaceBasedClass, $this->object->generate("ClassGeneratorTest_Interface", "ClassGeneratorTest_Interface_Generated"));
	}

	/**
	 * @expectedException Mockista\ClassGenerator\ClassGeneratorException
	 * @expectedExceptionCode 1
	 */
	function testGenerateThrowsExceptionOnFinalClass()
	{
		$this->object->setMethodFinder(new MethodFinder());
		$this->object->generate("ClassGeneratorTest_Final", "ClassGeneratorTest_Final_Generated");
	}

}
