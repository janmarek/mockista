<?php

namespace Mockista;

class MockCommon
{
	const MODE_LEARNING = 1;
	const MODE_COLLECTING = 2;

	const CALL_TYPE_EXACTLY = 1;
	const CALL_TYPE_AT_LEAST = 2;
	const CALL_TYPE_NO_MORE_THAN = 3;

	const INVOKE_STRATEGY_RETURN = 1;
	const INVOKE_STRATEGY_THROW = 2;
	const INVOKE_STRATEGY_CALLBACK = 3;

	protected $__mode = self::MODE_LEARNING;

	protected $__methods = array();

	protected $args;

	protected $callType;

	protected $callCount;

	protected $invokeStrategy;

	protected $thrownException;

	protected $calledCallback;

	protected $invokeValues = array();

	protected $invokeIndex = 0;

	protected $name = '';

	protected $callCountReal = 0;

	public function __construct($name = "", $args = array())
	{
		$this->name = $name;
		$this->args = $args;
	}

	public function assertExpectationsOnMyself()
	{
		$passed = true;
		$message = "";
		$code = 0;

		switch ($this->callType) {
			case self::CALL_TYPE_EXACTLY:
				$passed = $this->callCount == $this->callCountReal;
				$message = "Expected {$this->name} {$this->callCount} and called {$this->callCountReal}";
				$code = MockException::CODE_EXACTLY;
				break;
			case self::CALL_TYPE_AT_LEAST:
				$passed = $this->callCount <= $this->callCountReal;
				$message = "Expected {$this->name} at least {$this->callCount} and called {$this->callCountReal}";
				$code = MockException::CODE_AT_LEAST;
				break;
			case self::CALL_TYPE_NO_MORE_THAN:
				$passed = $this->callCount >= $this->callCountReal;
				$message = "Expected {$this->name} no more than {$this->callCount} and called {$this->callCountReal}";
				$code = MockException::CODE_NO_MORE_THAN;
				break;
		}

		if (!$passed) {
			throw new MockException($message, $code);
		}
	}

	public function assertExpectations()
	{
		$this->assertExpectationsOnMyself();

		foreach ($this->__methods as $method) {
			foreach ($method as $argCombinationMethod) {
				$argCombinationMethod->assertExpectations();
			}
		}
	}

	protected function checkMethodsNamespace($name)
	{
		if (!isset($this->__methods[$name])) {
			$this->__methods[$name] = array();
		}
	}

	protected function hashArgs($args)
	{
		if (array() == $args) {
			return 0;
		} else {
			$hash = "";

			foreach ($args as $arg) {
				$hash .= $this->hashArg($arg);
			}

			return md5($hash);
		}
	}

	protected function hashArg($arg) {
		if (is_object($arg)) {
			return spl_object_hash($arg);
		} else {
			try {
				return md5(serialize($arg));
			} catch (\Exception $e) {
				return md5(serialize(var_export($arg, TRUE)));
			}
		}
	}

	public function once()
	{
		$this->callType = self::CALL_TYPE_EXACTLY;
		$this->callCount = 1;

		return $this;
	}

	public function twice()
	{
		$this->callType = self::CALL_TYPE_EXACTLY;
		$this->callCount = 2;

		return $this;
	}

	public function never()
	{
		$this->callType = self::CALL_TYPE_EXACTLY;
		$this->callCount = 0;

		return $this;
	}

	public function exactly($count)
	{
		$this->callType = self::CALL_TYPE_EXACTLY;
		$this->callCount = $count;

		return $this;
	}


	public function atLeastOnce()
	{
		$this->callType = self::CALL_TYPE_AT_LEAST;
		$this->callCount = 1;

		return $this;
	}

	public function atLeast($count)
	{
		$this->callType = self::CALL_TYPE_AT_LEAST;
		$this->callCount = $count;

		return $this;
	}

	public function noMoreThanOnce()
	{
		$this->callType = self::CALL_TYPE_NO_MORE_THAN;
		$this->callCount = 1;

		return $this;
	}

	public function noMoreThan($count)
	{
		$this->callType = self::CALL_TYPE_NO_MORE_THAN;
		$this->callCount = $count;

		return $this;

	}

	public function andReturn($returnValue)
	{
		$this->invokeStrategy = self::INVOKE_STRATEGY_RETURN;
		$this->invokeValues = func_get_args();
		$this->invokeIndex = 0;

		return $this;
	}

	public function andThrow($throwException)
	{
		$this->invokeStrategy = self::INVOKE_STRATEGY_THROW;
		$this->thrownException = $throwException;

		return $this;
	}

	public function andCallback($callback)
	{
		$this->invokeStrategy = self::INVOKE_STRATEGY_CALLBACK;
		$this->calledCallback = $callback;

		return $this;
	}

	public function __get($name)
	{
		return $this->$name();
	}

	public function freeze()
	{
		$this->__mode = self::MODE_COLLECTING;

		foreach ($this->__methods as $key1) {
			foreach($key1 as $key2 => $mockObject) {
				$mockObject->freeze();
			}
		}

		return $this;
	}

}