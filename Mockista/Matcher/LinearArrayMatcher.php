<?php

namespace Mockista\Matcher;

/**
 * @author Jan Marek
 */
class LinearArrayMatcher implements MatcherInterface
{

	/** @var MatcherInterface[] */
	private $matchers;

	public function __construct($args)
	{
		$this->matchers = array();

		foreach ($args as $arg) {
			if ($arg instanceof MatcherInterface) {
				$this->matchers[] = $arg;
			} else {
				$this->matchers[] = new ValueMatcher($arg);
			}
		}
	}

	public function match($arg)
	{
		if (!is_array($arg)) {
			return FALSE;
		}

		if (count($arg) !== count($this->matchers)) {
			return FALSE;
		}

		foreach (array_values($arg) as $i => $value) {
			if (!$this->matchers[$i]->match($value)) {
				return FALSE;
			}
		}

		return TRUE;
	}

} 