<?php

namespace Mockista\Matcher;

/**
 * @author Jan Marek
 */
class CallbackMatcher implements MatcherInterface
{

	private $callback;

	public function __construct($callback)
	{
		$this->callback = $callback;
	}

	public function match($arg)
	{
		return call_user_func($this->callback, $arg);
	}

} 