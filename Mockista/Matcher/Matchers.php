<?php

namespace Mockista\Matcher;

/**
 * @author Jan Marek
 */
class Matchers
{

	public static function isBool()
	{
		return new CallbackMatcher('is_bool');
	}

	public static function isNumeric()
	{
		return new CallbackMatcher('is_numeric');
	}

	public static function isInt()
	{
		return new CallbackMatcher('is_int');
	}

	public static function isFloat()
	{
		return new CallbackMatcher('is_float');
	}

	public static function isString()
	{
		return new CallbackMatcher('is_string');
	}

	public static function isArray()
	{
		return new CallbackMatcher('is_array');
	}

	public static function regexp($pattern)
	{
		return new RegexpMatcher($pattern);
	}

	public static function callback($callback)
	{
		return new CallbackMatcher($callback);
	}

} 