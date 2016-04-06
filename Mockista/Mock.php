<?php

namespace Mockista;

class Mock implements MockInterface
{

	protected $methods = array();

	private $name = NULL;

	private $frozen = FALSE;

	public function assertExpectations()
	{
		if ($this->frozen) {
			return $this->__call(__FUNCTION__, func_get_args());
		}
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
		if ($this->frozen) {
			return $this->__call(__FUNCTION__, func_get_args());
		}
		return $this->name;
	}

	/**
	 * Set user defined name
	 * @param string $name
	 */
	public function setName($name)
	{
		if ($this->frozen) {
			return $this->__call(__FUNCTION__, func_get_args());
		}
		$this->name = $name;
	}

	/**
	 * Freeze mock and prevent from default method collisions
	 * @return mixed|null
	 */
	public function freeze()
	{
		if ($this->frozen) {
			return $this->__call(__FUNCTION__, func_get_args());
		}
		$this->frozen = TRUE;
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
		if ($this->frozen) {
			return $this->__call(__FUNCTION__, func_get_args());
		}

		$this->checkMethodsNamespace($name);
		$method = new Method();
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