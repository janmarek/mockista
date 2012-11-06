<?php

namespace Mockista;

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
			$mock = new $newName();
			$mock->mockista = new Mock();
		} else {
			$mock = new Mock();
		}

		return $mock;
	}

	private static function fillDefaults($mock, $defaults)
	{
		foreach ($defaults as $key => $default) {
			if ($default instanceof \Closure) {
				$mock->expects($key)->andCallback($default);
			} else {
				$mock->$key = $default;
			}
		}

		return $mock;
	}

}