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
			'returnType' => PHP_VERSION_ID >= 70000 ? (string) $method->getReturnType() : NULL,
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
				'variadic' => FALSE,
			);

			$parameterDesc['passedByReference'] = $parameter->isPassedByReference();

			if ($parameter->isOptional()) {
				if ($parameter->getDeclaringClass()->isInternal() === FALSE && $parameter->isDefaultValueAvailable()) {
					$parameterDesc['default'] = $parameter->getDefaultValue();
				} elseif (PHP_VERSION_ID >= 50600 && $parameter->isVariadic()) {
					$parameterDesc['variadic'] = TRUE;
				} else {
					$parameterDesc['default'] = NULL;
				}
			}
			if ($parameter->isArray()) {
				$parameterDesc['typehint'] = 'array';
			} elseif (PHP_VERSION_ID >= 50400 && $parameter->isCallable()) {
				$parameterDesc['typehint'] = 'callable';
			} else {
				$klass = $parameter->getClass();
				if ($klass) {
					$parameterDesc['typehint'] = '\\' . $klass->getName();
				}
				else {
					if (PHP_VERSION_ID >= 70000) {
						$parameterDesc['typehint'] = (string) $parameter->getType();
					}
					else {
						$parameterDesc['typehint'] = null;
					}
				}
			}
			$out[$parameter->getPosition()] = $parameterDesc;
		}

		return $out;
	}

}
