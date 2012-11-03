<?php

namespace Mockista;

class LazyProxyGenerator extends BaseClassGenerator
{

	function generate($inheritedClass, $newName)
	{
		$methods = $this->methodFinder->methods($inheritedClass);
		list($out, $inheritedClass) = $this->namespaceCheck("", $inheritedClass);

		$out .= "class $newName extends $inheritedClass\n{\n	private \$__instance;\n	private \$__constructorArgs = array();";
		$out .= '
	function __construct()
	{
		$this->__constructorArgs = func_get_args();
	}

	private function __constructInstance()
	{
		if (! $this->__instance) {
			$classGenerator = new \ReflectionClass("'.$inheritedClass.'");
			$this->__instance = $classGenerator->newInstanceArgs($this->__constructorArgs);
		}
	}

	function __call($name, $args)
	{
		$this->__constructInstance();
		return call_user_func_array(array($this->__instance, $name), $args);
	}
';
		foreach ($methods as $name => $method) {
			$out .= $this->generateMethod($name, $method);
		}
		$out .= "}\n";
		return $out;
	}

	private function generateMethod($methodName, $method)
	{
		if ($methodName !== '__construct') {
			$params = $this->generateParams($method['parameters']);
			$static = $method['static'] ? 'static ' : '';
		$passedByReference = $method['passedByReference'] ? '&' : '';
			$out = "
	{$static}function $passedByReference$methodName($params)
	{
		\$this->__constructInstance();
		return call_user_func_array(array(\$this->__instance, '$methodName'), func_get_args());
	}
";
			return $out;
		}
	}
}

