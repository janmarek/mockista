<?php

namespace Mockista\ClassGenerator;

class ClassGenerator
{

	protected $methodFinder;

	public function setMethodFinder($methodFinder)
	{
		return $this->methodFinder = $methodFinder;
	}

	protected function generateParams($params)
	{
		$out = array();

		foreach ($params as $param) {
			if ($param['typehint']) {
				$outArr = $param['typehint'] . ' ';
			} else {
				$outArr = '';
			}

			if ($param['passedByReference']) {
				$outArr .= '&';
			}

			if ($param['variadic']) {
				$outArr .= '...';
			}

			$outArr .= '$' . $param['name'];
			if (array_key_exists('default', $param)) {
				$outArr .= ' = ' . $this->removeNewLines(
						var_export($param['default'], true)
					);
			}
			$out[] = $outArr;
		}

		return join(', ', $out);
	}

	protected function removeNewLines($str)
	{
		return str_replace("\n", "", $str);
	}


	protected function containsNamespace($str)
	{
		return false !== strpos($str, "\\");
	}

	protected function removeNameSpace($str)
	{
		return substr($str, strrpos($str, "\\") + 1);
	}

	protected function namespaceCheck($out, $inheritedClass)
	{
		if ($this->containsNamespace($inheritedClass)) {
			$out .= "use {$inheritedClass};\n\n";
			$inheritedClass = $this->removeNameSpace($inheritedClass);
		}

		return array($out, $inheritedClass);
	}

	public function generate($inheritedClass, $newName)
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
';

		if (isset($methods['__call'])) {
			$parameters = $methods['__call']['parameters'];
			$callNameParamName = '$' . $parameters[0]['name'];
			$callArgsParamName = '$' . $parameters[1]['name'];
			$callParams = $this->generateParams($methods['__call']['parameters']);
		} else {
			$callNameParamName = '$name';
			$callArgsParamName = '$args';
			$callParams = '$name, $args';
		}


		$out .= "
	function __call($callParams)
	{
		\$l = call_user_func_array(array(\$this->mockista, $callNameParamName), $callArgsParamName);
		return \$l;
	}
";

		foreach ($methods as $name => $method) {
			if ("__call" == $name || "__construct" == $name || $method['final']) {
				continue;
			}
			if ("__destruct" == $name) {
				$out .= '
	function __destruct()
	{
	}
'; 			continue;
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
		$returnType = $method['returnType'] ? ' : ' . ($method['returnType']['allowsNull'] ? '?' : '') . $method['returnType']['typehint'] : '';
		$out = "
	{$static}function $passedByReference$methodName($params)$returnType
	{
		\$l = call_user_func_array(array(\$this->mockista, '$methodName'), func_get_args());
		return \$l;
	}
";

		return $out;
	}

}
