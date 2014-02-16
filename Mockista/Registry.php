<?php

namespace Mockista;

class Registry
{

	/** @var MockInterface[] */
	private $mocks = array();

	private $mockId = 1;

	/**
	 * Create mock
	 * @param string $class mocked class
	 * @param array $methods
	 * @return MockInterface
	 */
	public function create($class = NULL, array $methods = array())
	{
		return $this->createBuilder($class, $methods)->getMock();
	}

	/**
	 * Create mock with user defined name
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
	 * Create mock builder
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
	 * Create builder for named mock
	 *
	 * @param string $name user defined name
	 * @param string $class
	 * @param array $methods
	 * @return MockBuilder
	 */
	public function createNamedBuilder($name, $class = NULL, array $methods = array())
	{
		$builder = new MockBuilder($class, $methods);
		$mock = $builder->getMock();
		if (isset($this->mocks[$name])) {
			throw new InvalidArgumentException("Mock with name {$name} is already registered.");
		}
		$mock->setName($name);
		$this->mocks[$name] = $mock;
		$this->mockId++;

		return $builder;
	}

	/**
	 * Get named mock
	 * @param string $name
	 * @return MockInterface
	 */
	public function getMockByName($name)
	{
		if (!isset($this->mocks[$name])) {
			throw new InvalidArgumentException("There is no mock named {$name} in the registry");
		}

		return $this->mocks[$name];
	}

	/**
	 * Get builder for named mock
	 * @param string $name
	 * @return MockBuilder
	 */
	public function getBuilderByName($name)
	{
		return MockBuilder::createFromMock($this->getMockByName($name));
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
