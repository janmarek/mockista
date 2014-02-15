<?php

namespace Mockista;

use Mockista\ClassGenerator\ClassGenerator;
use Mockista\ClassGenerator\MethodFinder;

class MockBuilder
{

	/** @var Mock */
	private $mock;

	public function __construct($classOrMock = NULL, array $defaults = array())
	{

		if (is_array($classOrMock)) {
			$defaults = $classOrMock;
			$classOrMock = NULL;
		} else if (is_object($classOrMock) && ($classOrMock instanceOf Mock || property_exists($classOrMock, 'mockista'))) {
			$this->mock = $classOrMock;
		}

		if ($this->mock === NULL) {
			$this->mock = $this->createMock($classOrMock);
		}

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

			$newName = str_replace("\\", "_", $class) . '_' . mt_rand();
			$code = $classGenerator->generate($class, $newName);

			eval($code);
			$mock = new $newName();
			$mock->mockista = new Mock();
			$mock->mockista->setMockName($class);
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
