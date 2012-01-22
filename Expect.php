<?php

namespace Mockista;
require_once "PHPUnit/Framework/Assert/Functions.php";

function I_expect($value = null)
{
	return new Expectation($value);
}

class Expectation
{
	public $value;

	private static $callbacks = array();

	function __construct($value)
	{
		$this->value = $value;
	}

	static function setCallback($name, $callback)
	{
		self::$callbacks[$name] = $callback;
	}

	function __get($name)
	{
		return $this->$name();
	}

	function __call($name, $args = array())
	{
		return call_user_func_array(self::$callbacks[$name], array_merge(array($this), $args));
	}

}

Expectation::setCallback("isTrue", function ($self) {
	assertTrue($self->value);
});

Expectation::setCallback("isFalse", function ($self) {
	assertFalse($self->value);
});

Expectation::setCallback("isEqualTo", function ($self, $value) {
	assertEquals($self->value, $value);
});
