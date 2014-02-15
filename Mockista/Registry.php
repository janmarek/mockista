<?php

namespace Mockista;

class Registry
{

	/** @var MockInterface[] */
	private $mocks = array();

	private $mockId = 1;

	/**
	 * @param string $class
	 * @param array $methods
	 * @return MockInterface
	 */
	public function create($class = NULL, array $methods = array())
	{
		return $this->createBuilder($class, $methods)->getMock();
	}

	/**
	 * @param string $name
	 * @param string $class
	 * @param array $methods
	 * @return MockInterface
	 */
	public function createNamed($name, $class = NULL, array $methods = array())
	{
		return $this->createNamedBuilder($name, $class, $methods)->getMock();
	}

	/**
	 * @param string $class
	 * @param array $methods
	 * @return MockBuilder
	 */
	public function createBuilder($class = NULL, array $methods = array())
	{
		$name = $class ? "{$class}#{$this->mockId}" : "#{$this->mockId}";
		return $this->createNamedBuilder($name, $class, $methods);
	}

	/**
	 * @param string $name
	 * @param string $class
	 * @param array $methods
	 * @return MockBuilder
	 */
	public function createNamedBuilder($name, $class = NULL, array $methods = array()) {
		$builder = new MockBuilder($class, $methods);
		$mock = $builder->getMock();
		if (isset($this->mocks[$name])) {
			throw new MockException("Mock with name {$name} is already registered.");
		}
		$mock->setMockName($name);
		$this->mocks[$name] = $mock;
		$this->mockId++;
		return $builder;
	}

	public function getMock($name) {
		if (!isset($this->mocks[$name])) {
			throw new MockException("There is no mock named {$name} in the registry");
		}
		return $this->mocks[$name];
	}

	public function getBuilder($name) {
		return new MockBuilder($this->getMock($name));
	}

	/**
	 * Assert expectations on all created mocks
	 *
	 * This method should be called in tearDown method of test case.
	 */
	public function assertExpectations()
	{
		foreach ($this->mocks as $mock) {
			$mock->assertExpectations();
		}
	}

}
