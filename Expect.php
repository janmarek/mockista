<?php

namespace Mockista;
use PHPUnit_Framework_Assert;

require_once "PHPUnit/Framework/Assert.php";

function I_expect($value = null)
{
	return new Expectation($value);
}

function expect($value = null)
{
	return I_expect($value);
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
		if (array_key_exists($name, self::$callbacks)) {
			return call_user_func_array(self::$callbacks[$name], array_merge(array($this), $args));
		} else {
			return call_user_func_array(array("PHPUnit_Framework_Assert", "assert" . ucfirst($name)), array_merge(array($this), $args));
		}
	}
}

Expectation::setCallback("isTrue", function ($self) {
	PHPUnit_Framework_Assert::assertTrue($self->value);
});

Expectation::setCallback("isFalse", function ($self) {
	PHPUnit_Framework_Assert::assertFalse($self->value);
});

Expectation::setCallback("isEqualTo", function ($self, $value) {
	PHPUnit_Framework_Assert::assertEquals($value, $self->value);
});

Expectation::setCallback("sizeIs", function ($self, $value) {
	PHPUnit_Framework_Assert::assertEquals($value, sizeof($self->value));
});



