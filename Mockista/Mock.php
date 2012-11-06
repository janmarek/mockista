<?php

namespace Mockista;

class Mock extends MockCommon implements MethodInterface
{

	protected function useHash($name, $args, $hash)
	{
		if ($hash !== 0 && isset($this->__methods[$name][$hash])) {
			return $hash;
		} else if (isset($this->__methods[$name][0])) {
			return 0;
		} else {
			$argsStr = var_export($args, true);
			throw new MockException("Unexpected call in method: $name args: $argsStr", MockException::CODE_INVALID_ARGS);
		}
	}

	public function __call($name, $args)
	{
		$hash = $this->hashArgs($args);
		$this->checkMethodsNamespace($name);
		if (self::MODE_LEARNING == $this->__mode) {
			$this->__methods[$name][$hash] = new Mock($name, $args);
			return $this->__methods[$name][$hash];
		} else if (self::MODE_COLLECTING == $this->__mode) {
			$useHash = $this->useHash($name, $args, $hash);
			return $this->__methods[$name][$useHash]->invoke($args);
		}
	}

	public function invoke($args)
	{
		$this->callCountReal++;

		switch ($this->invokeStrategy) {
			case self::INVOKE_STRATEGY_RETURN:
				$out = $this->invokeValues[$this->invokeIndex];
				if ($this->invokeIndex < sizeof($this->invokeValues) - 1) {
					$this->invokeIndex++;
				}
				return $out;
				break;
			case self::INVOKE_STRATEGY_THROW:
				throw $this->thrownException;
				break;
			case self::INVOKE_STRATEGY_CALLBACK:
				return call_user_func_array($this->calledCallback, $args);
			default:
				if (isset($this->__methods[$this->name][$this->hashArgs($args)])) {
					return $this->__methods[$this->name][$this->hashArgs($args)];
				}
		}
	}

}