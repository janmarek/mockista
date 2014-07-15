<?php

namespace Mockista\Matcher;

/**
 * @author Jan Marek
 */
class RegexpMatcher implements MatcherInterface
{

	private $pattern;

	public function __construct($pattern)
	{
		$this->pattern = $pattern;
	}

	public function match($arg)
	{
		return is_string($arg) && preg_match($this->pattern, $arg) === 1;
	}

}