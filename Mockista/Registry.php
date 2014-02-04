<?php

namespace Mockista;

class Registry
{

	/** @var MockInterface[] */
	private $mocks = array();

	/** @var ArgsMatcher */
	private $argsMatcher;

	public function __construct(ArgsMatcher $argsMatcher = NULL)
	{
		$this->argsMatcher = $argsMatcher;
	}

	public function setArgsMatcher(ArgsMatcher $argsMatcher = NULL)
	{
		$this->argsMatcher = $argsMatcher;
	}

	/**
	 * @param string $class
	 * @param array $methods
	 * @param ArgsMatcher $argsMatcher
	 * @return MockInterface
	 */
	public function create($class = NULL, array $methods = array(), ArgsMatcher $argsMatcher = NULL)
	{
		return $this->createBuilder($class, $methods, $argsMatcher)->getMock();
	}

	/**
	 * @param string $class
	 * @param array $methods
	 * @return MockBuilder
	 */
	public function createBuilder($class = NULL, array $methods = array(), ArgsMatcher $argsMatcher = NULL)
	{
		if ($argsMatcher === NULL) {
			$argsMatcher = $this->argsMatcher;
		}
		$builder = new MockBuilder($class, $methods, $argsMatcher);
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
