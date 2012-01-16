<?php

use Mockista\MethodInterface;

require_once __DIR__ . "/bootstrap.php";

class ClassGeneratorTest_Empty
{
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
}
';
		$this->object->setMethodFinder($this->mockNoMethods);
		$this->assertEquals($emptyClass, $this->object->generate("ClassGeneratorTest_Empty", "ClassGeneratorTest_Empty_Generated"));
	}

	function testMethod()
	{
		$this->markTestIncomplete();
		$classIncludingMethod = '<?php
class ClassGeneratorTest_Method_Generated extends ClassGeneratorTest_Method
{
	public $mockista;

	function abc($def = 123)
	{
	}
}
';
	}
}
