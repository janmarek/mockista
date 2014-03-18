<?php

namespace Mockista\ClassGenerator;

class MethodFinder
{

	function methods($class)
	{
		$class = new \ReflectionClass($class);
		$out = array();

		foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
			$out[$method->getName()] = $this->getMethodDescription($method);
		}

		return $out;
	}

	function getMethodDescription(\ReflectionMethod $method)
	{
		return array(
			'parameters' => $this->getParameters($method),
			'static' => $method->isStatic(),
			'passedByReference' => $this->isMethodPassedByReference($method),
			'final' => $method->isFinal(),
		);
	}

	private function isMethodPassedByReference($method)
	{
		return false !== strpos($method, '&');
	}

	function getParameters($method)
	{
		$parameters = $method->getParameters();
		$out = array();

		foreach ($parameters as $parameter) {
			$parameterDesc = array(
				'name' => $parameter->getName(),
				'typehint' => null,
			);

			$parameterDesc['passedByReference'] = $parameter->isPassedByReference();

			if ($parameter->isOptional()) {
				$parameterDesc['default'] = $parameter->getDefaultValue();
			}
			if ($parameter->isArray()) {
				$parameterDesc['typehint'] = 'array';
                        } elseif (PHP_VERSION_ID >= 50400 && $parameter->isCallable()) {
				$parameterDesc['typehint'] = 'callable';
			} else {
				$klass = $parameter->getClass();
				if ($klass) {
					$parameterDesc['typehint'] = $klass->getName();
				}
				else {
					$parameterDesc['typehint'] = null;
				}
			}
			$out[$parameter->getPosition()] = $parameterDesc;
		}

		return $out;
	}

}