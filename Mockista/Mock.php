<?php

namespace Mockista;

class Mock implements MockInterface
{

	protected $methods = array();

	private $mocked;

	private $argsMatcher;

	private $name = NULL;

	public function __construct($mocked = NULL)
	{
		$this->mocked = $mocked;
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
			$argsStr = '';

			foreach ($args as $arg) {
				ob_start();
				var_dump($arg);
				$lines = explode(PHP_EOL, ob_get_clean());
				for ($i = 0; $i < count($lines); $i++) {
					$argsStr .= PHP_EOL . ($i === 0 ? '- ' : '  ') . $lines[$i];
				}

			}
			$objectName = $this->name ? $this->name : 'unnammed';
			throw new MockException("Unexpected call in mock $objectName::$name(), args:\n$argsStr", MockException::CODE_INVALID_ARGS);
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
		$method->mocked = $this->mocked;
		$this->methods[$name][] = $method;
		return $method;
	}

	public function __call($name, $args)
	{
		try {
			$method = $this->findMethod($name, $args);
		} catch (MockException $e) {
			if ($this->mocked !== NULL) {
				$passTo = array($this->mocked, $name);
				if (!is_callable($passTo)) {
					throw new MockException("Cannot pass unexpected call to mocked object.".
						" Method '$name' in '" . get_class($this->mocked) . "' doesn't exists.", MockException::CODE_CANNOT_PASS);
				}
				return call_user_func_array($passTo, $args);
			} else {
				throw $e;
			}
		}
		return $method->invoke($args);
	}

}