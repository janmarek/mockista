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
		list($class, $defaults) = self::parseArgs(func_get_args());

		$mock = self::createMock($class);

		$mock = self::fillDefaults($mock, $defaults);

		return $mock;
	}

	private static function parseArgs($args)
	{
		$defaults = array();
		$class = false;
		$constructorArgs = array();

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
		return array($class, $defaults);
	}

	private static function createMock($class)
	{
		if ($class) {
			$classGenerator = new ClassGenerator;
			$classGenerator->setMethodFinder(new MethodFinder);
			
			$newName = str_replace("\\", "_", $class) . '_' . uniqid();
			$code = $classGenerator->generate($class, $newName);
			
			eval($code);
			$mock = new $newName(null, null, null, null, null, null, null, null, null, null);
			$mock->mockista = new Mock();
		} else {
			$mock = new Mock();
		}

		return $mock;
	}

	private static function fillDefaults($mock, $defaults)
	{

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

class MockCommon
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

	protected $args;

	protected $callType;
	protected $callCount;

	protected $invokeStrategy;
        protected $thrownException;
        protected $calledCallback;
	protected $invokeValues = array();
        protected $invokeIndex = 0;

	protected $name = '';

	protected $callCountReal = 0;

	public function __construct($name = "", $args = array())
	{
		$this->name = $name;
		$this->args = $args;
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

	public function assertExpectations()
	{
		$this->assertExpectationsOnMyself();
		foreach ($this->__methods as $method) {
			foreach ($method as $argCombinationMethod) {
				$argCombinationMethod->assertExpectations();
			}
		}
	}
	
	protected function checkMethodsNamespace($name)
	{
		if (! isset($this->__methods[$name])) {
			$this->__methods[$name] = array();
		}
	}

	protected function hashArgs($args)
	{
		if (array() == $args) {
			return 0;
		} else {
			$hash = "";
			foreach ($args as $arg) {
				$hash .= $this->hashArg($arg);
			}
			return md5($hash);
		}
	}
	protected function hashArg($arg) {
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
		$this->invokeValues = func_get_args();
                $this->invokeIndex = 0;
		return $this;
	}

	public function andThrow($throwException)
	{
		$this->invokeStrategy = self::INVOKE_STRATEGY_THROW;
		$this->thrownException = $throwException;
		return $this;
	}

	public function andCallback($callback)
	{
		$this->invokeStrategy = self::INVOKE_STRATEGY_CALLBACK;
		$this->calledCallback = $callback;
		return $this;
	}

	public function __get($name)
	{
		return $this->$name();
	}

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


}

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
		switch ($this->invokeStrategy) {
		case self::INVOKE_STRATEGY_RETURN:
			$this->callCountReal++;
			$out = $this->invokeValues[$this->invokeIndex];
                        if ($this->invokeIndex < sizeof($this->invokeValues) - 1) {
                            $this->invokeIndex++;
                        }
                        return $out;
			break;
		case self::INVOKE_STRATEGY_THROW:
			$this->callCountReal++;
			throw $this->thrownException;
			break;
		case self::INVOKE_STRATEGY_CALLBACK:
			$this->callCountReal++;
			return call_user_func_array($this->calledCallback, $args);

		default:
			$this->callCountReal++;
			if (isset($this->__methods[$this->name][$this->hashArgs($args)])) {
				return $this->__methods[$this->name][$this->hashArgs($args)];
			}
			break;
		}
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

	function getMethodDescription(\ReflectionMethod $method)
	{
		return array(
			'parameters'=>$this->getParameters($method),
			'static'=>$method->isStatic(),
			'passedByReference'=>$this->isMethodPassedByReference($method),
                        'final'=>$method->isFinal(),
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
				'name'=>$parameter->getName(),
				'typehint'=>null,	
								
			);

			$parameterDesc['passedByReference'] = $parameter->isPassedByReference();
			
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
				else {
					$parameterDesc['typehint'] = null;
				}
			}
			$out[$parameter->getPosition()] = $parameterDesc;
		}
		return $out;
	}
}



abstract class BaseClassGenerator
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
}

class ClassGeneratorException extends \RuntimeException
{
    const CODE_FINAL_CLASS_CANNOT_BE_MOCKED = 1;
}

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
            if (! class_exists($inheritedClass)) {
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

