<?php

namespace Mockista;

class ClassGenerator extends BaseClassGenerator
{

	function generate($inheritedClass, $newName)
	{
		$extends = class_exists($inheritedClass) ? "extends" : "implements";
		$methods = $this->methodFinder->methods($inheritedClass);

		list($out, $inheritedClass) = $this->namespaceCheck("", $inheritedClass);

		$isFinal = $this->isFinal($inheritedClass);
		if ($isFinal) {
			throw new ClassGeneratorException("Cannot mock final class", ClassGeneratorException::CODE_FINAL_CLASS_CANNOT_BE_MOCKED);
		}

		$out .= "class $newName $extends $inheritedClass\n{\n	public \$mockista;\n";
		$out .= '
	function __construct()
	{
	}

	function __call($name, $args)
	{
		return call_user_func_array(array($this->mockista, $name), $args);
	}
';
		foreach ($methods as $name => $method) {
			if ("__call" == $name || "__construct" == $name || $method['final']) {
				continue;
			}
			$out .= $this->generateMethod($name, $method);
		}
		$out .= "}\n";

		return $out;
	}

	private function isFinal($inheritedClass)
	{
		if (!class_exists($inheritedClass)) {
			return false;
		}
		$klass = new \ReflectionClass($inheritedClass);

		return $klass->isFinal();
	}

	private function generateMethod($methodName, $method)
	{
		$params = $this->generateParams($method['parameters']);
		$static = $method['static'] ? 'static ' : '';
		$passedByReference = $method['passedByReference'] ? '&' : '';
		$out = "
	{$static}function $passedByReference$methodName($params)
	{
		return call_user_func_array(array(\$this->mockista, '$methodName'), func_get_args());
	}
";

		return $out;
	}

}