<?php

namespace Mockista;

class Mock implements MockInterface
{

	protected $methods = array();

	private $argsMatcher;

	private $name = NULL;

	public function __construct()
	{
		$this->argsMatcher = new ArgsMatcher();
	}

	public function assertExpectations()
	{
		foreach ($this->methods as $method) {
			foreach ($method as $argCombinationMethod) {
				$argCombinationMethod->assertExpectations();
			}
		}
	}

	/**
	 * Get user defined name
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set user defined name
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	private function checkMethodsNamespace($name)
	{
		if (!isset($this->methods[$name])) {
			$this->methods[$name] = array();
		}
	}

	protected function findMethod($name, $args)
	{
		$this->checkMethodsNamespace($name);

		$best = NULL;

		foreach ($this->methods[$name] as $method) {
			if ($method->hasArgs() && $method->matchArgs($args)) {
				return $method;
			}

			if (!$method->hasArgs()) {
				$best = $method;
			}
		}

		if (!$best) {
			$argsStr = @var_export($args, TRUE); // intentionally used shut-up operator (@) to prevent "var_export does not handle circular references" warning
			$objectName = $this->name ? $this->name : 'unnammed';
			throw new MockException("Unexpected call in mock $objectName::$name(), args: $argsStr", MockException::CODE_INVALID_ARGS);
		}

		return $best;
	}

	/**
	 * @param string $name
	 * @return MethodInterface
	 */
	public function expects($name)
	{
		$this->checkMethodsNamespace($name);
		$method = new Method($this->argsMatcher);
		$method->owningMock = $this;
		$method->name = $name;
		$this->methods[$name][] = $method;
		return $method;
	}

	public function __call($name, $args)
	{
		return $this->findMethod($name, $args)->invoke($args);
	}

}
