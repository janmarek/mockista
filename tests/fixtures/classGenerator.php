<?php

class ClassGeneratorTest_Empty
{
}

final class ClassGeneratorTest_Final
{
}

class ClassGeneratorTest_Method
{
	function &abc(&$a, $def = 123, $ghi = 'a')
	{
	}

	function __construct($a) {
	}

	function __call($name, $args)
	{
	}

	final function finalMethod()
	{
	}

	function __destruct()
	{
	}
}

class ClassGeneratorTest_Typehinted__call {
	function __call($laname, array $argumentos) {
	}
}

interface ClassGeneratorTest_Interface
{
	function ai(Array $ax = array(1, 2, 3));
}