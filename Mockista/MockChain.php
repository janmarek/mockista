<?php

namespace Mockista;

class MockChain
{

	private $lastCalledMethods = array();

	function addLastCalledMethod($method, $mock)
	{
		$this->lastCalledMethods[$method] = $mock;
		return $this;
	}

	function __call($name, $args) {
		if (array_key_exists($name, $this->lastCalledMethods)) {
			return call_user_func_array(array($this->lastCalledMethods[$name], $name), $args);
		} else {
			return $this;
		}
	}

	function __get($name) {
		return $this;
	}
}