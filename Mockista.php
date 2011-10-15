<?php

namespace Mockista;
use Exception;

function mock()
{
	return new Mock();
}

class MockException extends \Exception
{
	const CODE_EXACTLY = 1;
	const CODE_AT_LEAST = 2;
	const CODE_NO_MORE_THAN = 3;
	const CODE_INVALID_ARGS = 4;
}

class Mock implements MockInterface
{
	const MODE_LEARNING = 1;
	const MODE_COLLECTING = 2;
	private $__mode = self::MODE_LEARNING;
	private $__methods = array();

	public function freeze()
	{
		$this->__mode = self::MODE_COLLECTING;
		return $this;
	}

	public function collect()
	{
		foreach ($this->__methods as $method) {
			$method->collect();
		}
	}

	public function __call($name, $args)
	{
		if (self::MODE_LEARNING == $this->__mode) {
			$this->__methods[$name] = new MockMethod($args);
			return $this->__methods[$name];
		} else if (self::MODE_COLLECTING == $this->__mode) {
			if (array_key_exists($name, $this->__methods)) {
				return $this->__methods[$name]->invoke($args);
			}
		}
	}
}


class MockMethod implements MethodInterface
{
	const CALL_TYPE_EXACTLY = 1;
	const CALL_TYPE_AT_LEAST = 2;
	const CALL_TYPE_NO_MORE_THAN = 3;

	const INVOKE_STRATEGY_RETURN = 1;
	const INVOKE_STRATEGY_THROW = 2;
	const INVOKE_STRATEGY_CALLBACK = 3;

	private $args;

	private $callType;
	private $callCount;

	private $invokeStrategy;
	private $invokeValue;

	private $callCountReal = 0;

	public function __construct($args)
	{
		$this->args = $args;
	}

	public function collect()
	{
		$passed = true;
		$message = "";
		$code = 0;

		switch ($this->callType) {
			case self::CALL_TYPE_EXACTLY:
				$passed = $this->callCount == $this->callCountReal;
				$message = "Expected {$this->callCount} and called {$this->callCountReal}";
				$code = MockException::CODE_EXACTLY;
				break;

			case self::CALL_TYPE_AT_LEAST:
				$passed = $this->callCount <= $this->callCountReal;
				$message = "Expected at least {$this->callCount} and called {$this->callCountReal}";
				$code = MockException::CODE_AT_LEAST;
				break;

			case self::CALL_TYPE_NO_MORE_THAN:
				$passed = $this->callCount >= $this->callCountReal;
				$message = "Expected no more than {$this->callCount} and called {$this->callCountReal}";
				$code = MockException::CODE_NO_MORE_THAN;
				break;
			
			default:
				break;
		}

		if (! $passed) {
			throw new MockException($message, $code);
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
		$this->invokeValue = $returnValue;
		return $this;
	}

	public function andThrow($throwException)
	{
		$this->invokeStrategy = self::INVOKE_STRATEGY_THROW;
		$this->invokeValue = $throwException;
		return $this;
	}

	public function andCallback($callback)
	{
		$this->invokeStrategy = self::INVOKE_STRATEGY_CALLBACK;
		$this->invokeValue = $callback;
		return $this;
	}

	public function invoke($args)
	{
		if ($args !== $this->args) {
			throw new MockException("Args are not same as expected", MockException::CODE_INVALID_ARGS);
		}
		switch ($this->invokeStrategy) {
			case self::INVOKE_STRATEGY_RETURN:
				$this->callCountReal++;
				return $this->invokeValue;
				break;
			case self::INVOKE_STRATEGY_THROW:
				$this->callCountReal++;
				throw $this->invokeValue;
				break;
			case self::INVOKE_STRATEGY_CALLBACK:
				$this->callCountReal++;
				return call_user_func_array($this->invokeValue, $args);
			
			default:
				$this->callCountReal++;
				return;
				break;
		}
	}
}
