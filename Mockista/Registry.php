<?php

namespace Mockista;

class Registry
{

	/** @var MockInterface[] */
	private $mocks = array();

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
	 * @param string $class
	 * @param array $methods
	 * @return MockBuilder
	 */
	public function createBuilder($class = NULL, array $methods = array())
	{
		$builder = new MockBuilder($class, $methods);
		$this->mocks[] = $builder->getMock();
		return $builder;
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
