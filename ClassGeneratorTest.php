<?php

use Mockista\MethodInterface;

require_once __DIR__ . "/bootstrap.php";

class ClassGeneratorTest_Empty
{
}

class ClassGeneratorTest_Method
{
	function abc($a, $def = 123, $ghi = 'a')
	{
	}
}

class ClassGeneratorTest extends KDev_Test
{
	function prepare()
	{
		$this->object = new Mockista\ClassGenerator;
	}

	function mockNoMethods()
	{
		$mock = Mockista\mock();
		$mock->methods()->once->andReturn(array());
		return $mock;
	}

	function testEmptyClass()
	{
		$emptyClass = '<?php
class ClassGeneratorTest_Empty_Generated extends ClassGeneratorTest_Empty
{
	public $mockista;

	function __call($name, $args)
	{
		return call_user_func_array(array($this->mockista, $name), $args);
	}
}
';
		$this->object->setMethodFinder($this->mockNoMethods);
		$this->assertEquals($emptyClass, $this->object->generate("ClassGeneratorTest_Empty", "ClassGeneratorTest_Empty_Generated"));
	}

	function testMethod()
	{
		$classIncludingMethod = '<?php
class ClassGeneratorTest_Method_Generated extends ClassGeneratorTest_Method
{
	public $mockista;

	function __call($name, $args)
	{
		return call_user_func_array(array($this->mockista, $name), $args);
	}

	function abc($a, $def = 123, $ghi = \'a\')
	{
		return call_user_func_array(array($this->mockista, \'abc\'), func_get_args());
	}
}
';
		$this->object->setMethodFinder(new Mockista\MethodFinder);
		$this->assertEquals($classIncludingMethod, $this->object->generate("ClassGeneratorTest_Method", "ClassGeneratorTest_Method_Generated"));

	}
}
