<?php

namespace Mockista;

class ArgsMatcher
{

	public function serializeArgs(array $arguments)
	{
		if (array() == $arguments) {
			return 0;
		} else {
			$hash = "";

			foreach ($arguments as $arg) {
				$hash .= $this->hashArg($arg);
			}

			return md5($hash);
		}
	}

	private function hashArg($arg)
	{
		if (is_object($arg)) {
			return spl_object_hash($arg);
		} else {
			try {
				return md5(serialize($arg));
			} catch (\Exception $e) {
				return md5(serialize(var_export($arg, TRUE)));
			}
		}
	}

	public function matchArgs(array $arguments, $serializedArgs)
	{
		return $this->serializeArgs($arguments) === $serializedArgs;
	}

}
