<?php
require_once dirname(__DIR__) . "/bootstrap.php";

class LazyProxyGeneratorTest_Auxiliary_Lazy_Loaded_Class
{
	public static $constructorCalled = false;
	private $x;

	public function __construct($x)
	{
		self::$constructorCalled = true;
		$this->x = $x;
	}

	public function x()
	{
		return $this->x;
	}
}

class LazyProxyGeneratorTest extends KDev_Test
{
	/**
	 * 
	 */
	public function prepare()
	{
		$this->object = new Mockista\LazyProxyGenerator;
		$this->object->setMethodFinder(new Mockista\MethodFinder);
	}

	/**
	 * 
	 */
	public function testGenerateProxy()
	{
		$obj = $this->object->generate("LazyProxyGeneratorTest_Auxiliary_Lazy_Loaded_Class", "LazyProxyGeneratorTest_Auxiliary_Lazy_Loaded_Class_Inherited");
		eval($obj);	
		$proxy = new LazyProxyGeneratorTest_Auxiliary_Lazy_Loaded_Class_Inherited(33);
		$this->assertFalse(LazyProxyGeneratorTest_Auxiliary_Lazy_Loaded_Class::$constructorCalled);
		$ret = $proxy->x();
		$this->assertEquals(33, $ret);
		$this->assertTrue(LazyProxyGeneratorTest_Auxiliary_Lazy_Loaded_Class::$constructorCalled);
	}
}
