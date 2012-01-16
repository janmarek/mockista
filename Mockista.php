<?php

namespace Mockista;

function mock()
{
	return call_user_func_array(array("Mockista\MockFactory", "create"), func_get_args());
}

class MockFactory
{
	static function create()
	{
		$args = func_get_args();

		$defaults = array();
		$class = false;

		if (1 == sizeof($args)) {
			if (is_array($args[0])) {
				$defaults = $args[0];
			} else {
				$class = $args[0];
			}
		} else if (2 == sizeof($args)) {
			$class = $args[0];
			$defaults = $args[1];
		}

		if ($class) {
			$classGenerator = new ClassGenerator;
			$classGenerator->setMethodFinder(new MethodFinder);
			
			$newName = $class . '_' . uniqid();
			$code = $classGenerator->generate($class, $newName);
			eval($code);
			$mock = new $newName;
			$mock->mockista = new Mock();
		} else {
			$mock = new Mock();
		}

		foreach ($defaults as $key=>$default) {
			if ($default instanceof \Closure) {
				$mock->$key()->andCallback($default);
			} else {
				$mock->$key = $default;
			}
		}
		return $mock;
	}
}

if (class_exists("\PHPUnit_Framework_AssertionFailedError")) {
	class MockException extends \PHPUnit_Framework_AssertionFailedError
	{
		const CODE_EXACTLY = 1;
		const CODE_AT_LEAST = 2;
		const CODE_NO_MORE_THAN = 3;
		const CODE_INVALID_ARGS = 4;
	}
} else {
	class MockException extends \Exception
	{
		const CODE_EXACTLY = 1;
		const CODE_AT_LEAST = 2;
		const CODE_NO_MORE_THAN = 3;
		const CODE_INVALID_ARGS = 4;
	}
}

class MockChain
{

	private $lastCalledMethods = array();

	function addLastCalledMethod($method, $mock)
	{
		$this->lastCalledMethods[$method] = $mock;
		return $this;
	}

	function __call($name, $args) {
		if (array_key_exists($name, $this->lastCalledMethods)) {
			return call_user_func_array(array($this->lastCalledMethods[$name], $name), $args);
		} else {
			return $this;
		}
	}

	function __get($name) {
		return $this;
	}
}

class Mock implements MethodInterface
{
	const MODE_LEARNING = 1;
	const MODE_COLLECTING = 2;

	const CALL_TYPE_EXACTLY = 1;
	const CALL_TYPE_AT_LEAST = 2;
	const CALL_TYPE_NO_MORE_THAN = 3;

	const INVOKE_STRATEGY_RETURN = 1;
	const INVOKE_STRATEGY_THROW = 2;
	const INVOKE_STRATEGY_CALLBACK = 3;

	protected $__mode = self::MODE_LEARNING;

	protected $__methods = array();

	private $args;

	private $callType;
	private $callCount;

	private $invokeStrategy;
	private $invokeValue;

	private $name = '';

	private $callCountReal = 0;

	public function freeze()
	{
		$this->__mode = self::MODE_COLLECTING;
		foreach ($this->__methods as $key1) {
			foreach($key1 as $key2=>$mockObject) {
				$mockObject->freeze();
			}
		}
		return $this;
	}


	public function assertExpectations()
	{
		$this->assertExpectationsOnMyself();
		foreach ($this->__methods as $method) {
			foreach ($method as $argCombinationMethod) {
				$argCombinationMethod->assertExpectations();
			}
		}
	}

	protected function hashArgs($args)
	{
		if (array() == $args) {
			return 0;
		} else {
			try {
				return md5(serialize($args));
			} catch (\Exception $e) {
				return md5(serialize(var_export($args, TRUE)));
			}
		}
	}

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

	protected function checkMethodsNamespace($name)
	{
		if (! isset($this->__methods[$name])) {
			$this->__methods[$name] = array();
		}
	}

	public function __construct($name = "", $args = array())
	{
		$this->name = $name;
		$this->args = $args;
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

	public function assertExpectationsOnMyself()
	{
		$passed = true;
		$message = "";
		$code = 0;

		switch ($this->callType) {
		case self::CALL_TYPE_EXACTLY:
			$passed = $this->callCount == $this->callCountReal;
			$message = "Expected {$this->name} {$this->callCount} and called {$this->callCountReal}";
			$code = MockException::CODE_EXACTLY;
			break;

		case self::CALL_TYPE_AT_LEAST:
			$passed = $this->callCount <= $this->callCountReal;
			$message = "Expected {$this->name} at least {$this->callCount} and called {$this->callCountReal}";
			$code = MockException::CODE_AT_LEAST;
			break;

		case self::CALL_TYPE_NO_MORE_THAN:
			$passed = $this->callCount >= $this->callCountReal;
			$message = "Expected {$this->name} no more than {$this->callCount} and called {$this->callCountReal}";
			$code = MockException::CODE_NO_MORE_THAN;
			break;

		default:
			break;
		}

		if (! $passed) {
			throw new MockException($message, $code);
		}
	}

	public function invoke($args)
	{
		switch ($this->invokeStrategy) {
		case self::INVOKE_STRATEGY_RETURN:
			$this->callCountReal++;
			return $this->invokeValue;
			break;
		case self::INVOKE_STRATEGY_THROW:
			$this->callCountReal++;
			throw $this->invokeValue;
			break;
		case self::INVOKE_STRATEGY_CALLBACK:
			$this->callCountReal++;
			return call_user_func_array($this->invokeValue, $args);

		default:
			$this->callCountReal++;
			if (isset($this->__methods[$this->name][$this->hashArgs($args)])) {
				return $this->__methods[$this->name][$this->hashArgs($args)];
			}
			break;
		}
	}

	public function once()
	{
		$this->callType = self::CALL_TYPE_EXACTLY;
		$this->callCount = 1;
		return $this;
	}

	public function twice()
	{
		$this->callType = self::CALL_TYPE_EXACTLY;
		$this->callCount = 2;
		return $this;
	}

	public function never()
	{
		$this->callType = self::CALL_TYPE_EXACTLY;
		$this->callCount = 0;
		return $this;
	}

	public function exactly($count)
	{
		$this->callType = self::CALL_TYPE_EXACTLY;
		$this->callCount = $count;
		return $this;
	}


	public function atLeastOnce()
	{
		$this->callType = self::CALL_TYPE_AT_LEAST;
		$this->callCount = 1;
		return $this;
	}

	public function atLeast($count)
	{
		$this->callType = self::CALL_TYPE_AT_LEAST;
		$this->callCount = $count;
		return $this;
	}

	public function noMoreThanOnce()
	{
		$this->callType = self::CALL_TYPE_NO_MORE_THAN;
		$this->callCount = 1;
		return $this;
	}

	public function noMoreThan($count)
	{
		$this->callType = self::CALL_TYPE_NO_MORE_THAN;
		$this->callCount = $count;
		return $this;

	}

	public function andReturn($returnValue)
	{
		$this->invokeStrategy = self::INVOKE_STRATEGY_RETURN;
		$this->invokeValue = $returnValue;
		return $this;
	}

	public function andThrow($throwException)
	{
		$this->invokeStrategy = self::INVOKE_STRATEGY_THROW;
		$this->invokeValue = $throwException;
		return $this;
	}

	public function andCallback($callback)
	{
		$this->invokeStrategy = self::INVOKE_STRATEGY_CALLBACK;
		$this->invokeValue = $callback;
		return $this;
	}

	public function __get($name)
	{
		return $this->$name();
	}

}


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

	function getMethodDescription($method)
	{
		return array(
			'parameters'=>$this->getParameters($method),
			'static'=>$method->isStatic(),
		);
	}

	function getParameters($method)
	{
		$parameters = $method->getParameters();
		$out = array();
		foreach ($parameters as $parameter) {
			$parameterDesc = array(
				'name'=>$parameter->getName(),
			);
			if ($parameter->isOptional()) {
				$parameterDesc['default'] = $parameter->getDefaultValue();
			}
			if ($parameter->isArray()) {
				$parameterDesc['typehint'] = 'Array';
			} else {
				$klass = $parameter->getClass();
				if ($klass) {
					$parameterDesc['typehint'] = $klass->getName();
				}
			}
			$out[$parameter->getPosition()] = $parameterDesc;
		}
		return $out;
	}
}

class ClassGenerator
{
	private $methodsFinder;

	public function setMethodFinder($methodFinder)
	{
		return $this->methodFinder = $methodFinder;
	}
	
	function generate($inheritedClass, $newName)
	{
		$extends = class_exists($inheritedClass) ? "extends" : "implements";
		$methods = $this->methodFinder->methods($inheritedClass);

		$out = "class $newName $extends $inheritedClass\n{\n	public \$mockista;\n";
		$out .= '
	function __call($name, $args)
	{
		return call_user_func_array(array($this->mockista, $name), $args);
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
		$params = $this->generateParams($method['parameters']);
		$static = $method['static'] ? 'static ' : '';
		$out = "
	{$static}function $methodName($params)
	{
		return call_user_func_array(array(\$this->mockista, '$methodName'), func_get_args());
	}
";
		return $out;
	}

	private function generateParams($params)
	{
		$out = array();
		foreach ($params as $param) {

			if ($param['typehint']) {
				$outArr = $param['typehint'] . ' ';
			} else {
				$outArr = '';
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

	private function removeNewLines($str)
	{
		return str_replace("\n", "", $str);
	}
}
