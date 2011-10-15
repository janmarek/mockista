<?php

namespace Mockista;
use Exception;

function mock()
{
	return new Mock();
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
	}

	public function collect()
	{
		$collect = array();
		foreach ($this->methods as $method) {
			$collect = array_merge($collect, $method->collect());
		}
		return $collect;
	}

	public function __call($name, $args)
	{
		if (self::MODE_LEARNING == $this->__mode) {
			$this->__methods[$name] = new MockMethod($name, $args);
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

	private $name;
	private $args;

	private $callType;
	private $callCount;

	private $invokeStrategy;
	private $invokeValue;

	public function __construct($name, $args)
	{
		$this->name = $name;
		$this->args = $args;
	}

	public function collect()
	{
		return array();
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
			throw new Exception(); // TODO
		}
		switch ($this->invokeStrategy) {
			case self::INVOKE_STRATEGY_RETURN:
				return $this->invokeValue;
				break;
			case self::INVOKE_STRATEGY_THROW:
				throw $this->invokeValue;
				break;
			
			default:
				throw new Exception(); // TODO
				break;
		}
	}
}
