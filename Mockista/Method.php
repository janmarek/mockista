<?php

namespace Mockista;

class Method implements MethodInterface
{

	const CALL_TYPE_EXACTLY = 1;
	const CALL_TYPE_AT_LEAST = 2;
	const CALL_TYPE_NO_MORE_THAN = 3;

	const INVOKE_STRATEGY_RETURN = 1;
	const INVOKE_STRATEGY_THROW = 2;
	const INVOKE_STRATEGY_CALLBACK = 3;

	public $owningMock = NULL;

	public $name = '';

	protected $args = NULL;

	protected $callType;

	protected $callCount;

	protected $invokeStrategy;

	protected $thrownException;

	protected $calledCallback;

	protected $invokeValues = array();

	protected $invokeIndex = 0;

	protected $callCountReal = 0;

	private $argsMatcher;

	public function __construct(ArgsMatcher $argsMatcher)
	{
		$this->argsMatcher = $argsMatcher;
	}

	public function __get($name)
	{
		return $this->$name();
	}

	public function with()
	{
		$this->args = $this->argsMatcher->serializeArgs(func_get_args());

		return $this;
	}

	public function matchArgs($arguments)
	{
		return $this->argsMatcher->serializeArgs($arguments) === $this->args;
	}

	public function hasArgs()
	{
		return $this->args !== NULL;
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

	public function invoke($args)
	{
		$this->callCountReal++;

		switch ($this->invokeStrategy) {
			case self::INVOKE_STRATEGY_RETURN:
				$out = $this->invokeValues[$this->invokeIndex];
				if ($this->invokeIndex < sizeof($this->invokeValues) - 1) {
					$this->invokeIndex++;
				}
				return $out;
				break;
			case self::INVOKE_STRATEGY_THROW:
				throw $this->thrownException;
				break;
			case self::INVOKE_STRATEGY_CALLBACK:
				return call_user_func_array($this->calledCallback, $args);
		}
	}

	public function assertExpectations()
	{
		$passed = TRUE;
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

}
