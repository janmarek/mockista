<?php

namespace Mockista;

class Mock implements MockInterface
{

	protected $methods = array();

	public $mockName = NULL;

	/** @var ArgsMatcher */
	private $argsMatcher;

	public function __construct(ArgsMatcher $argsMatcher = NULL)
	{
		if ($argsMatcher === NULL) {
			$argsMatcher = new ArgsMatcher();
		}
		$this->argsMatcher = $argsMatcher;
	}

	public function assertExpectations()
	{
		foreach ($this->methods as $method) {
			foreach ($method as $argCombinationMethod) {
				$argCombinationMethod->assertExpectations();
			}
		}
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
			$argsStr = var_export($args, TRUE);
			$objectName = $this->mockName ? $this->mockName : 'unnammed';
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