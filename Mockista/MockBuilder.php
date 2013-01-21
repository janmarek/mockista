<?php

namespace Mockista;

use Mockista\ClassGenerator\ClassGenerator;
use Mockista\ClassGenerator\MethodFinder;

class MockBuilder
{

	/** @var Mock */
	private $mock;

	public function __construct($className = NULL, array $defaults = array())
	{
		if (is_array($className)) {
			$defaults = $className;
			$className = NULL;
		}

		$this->mock = $this->createMock($className);
		$this->addMethods($defaults);
	}

	public function __call($methodName, array $args = array())
	{
		$method = $this->mock->expects($methodName);
		call_user_func_array(array($method, 'with'), $args);
		return $method;
	}

	private function createMock($class)
	{
		if ($class) {
			$classGenerator = new ClassGenerator();
			$classGenerator->setMethodFinder(new MethodFinder());

			$newName = str_replace("\\", "_", $class) . '_' . uniqid();
			$code = $classGenerator->generate($class, $newName);

			eval($code);
			$mock = new $newName();
			$mock->mockista = new Mock();
			$mock->mockista->mockName = $class;
		} else {
			$mock = new Mock();
		}

		return $mock;
	}

	public function addMethods(array $methods)
	{
		foreach ($methods as $key => $default) {
			if ($default instanceof \Closure) {
				$this->mock->expects($key)->andCallback($default);
			} else {
				$this->mock->expects($key)->andReturn($default);
			}
		}
	}

	/**
	 * @return Mock
	 */
	public function getMock()
	{
		return $this->mock;
	}

}
