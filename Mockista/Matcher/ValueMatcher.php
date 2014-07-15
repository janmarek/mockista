<?php

namespace Mockista\Matcher;

/**
 * @author Jan Marek
 */
class ValueMatcher implements MatcherInterface
{

	private $value;

	public function __construct($value)
	{
		$this->value = $this->hashArg($value);
	}

	public function match($arg)
	{
		return $this->hashArg($arg) === $this->value;
	}

	private function hashArg($arg)
	{
		if (is_object($arg)) {
			return spl_object_hash($arg);
		} else {
			try {
				return md5(serialize($arg));
			} catch (\Exception $e) {
				ob_start();
				var_dump($arg);
				return md5(ob_get_clean());
			}
		}
	}

}