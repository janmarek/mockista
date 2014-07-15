<?php

namespace Mockista\Matcher;

/**
 * @author Jan Marek
 */
interface MatcherInterface
{

	/**
	 * @param mixed $arg
	 * @return bool
	 */
	public function match($arg);

} 