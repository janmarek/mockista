<?php

interface MethodFinderTest_Dummy1234If2
{
}

interface MethodFinderTest_Dummy1234If1
{
}

class MethodFinderTest_Dummy1234Parent implements MethodFinderTest_Dummy1234If1
{
	final function ab()
	{
	}
}

class MethodFinderTest_Dummy1234 extends MethodFinderTest_Dummy1234Parent implements MethodFinderTest_Dummy1234If2
{
	static function b(array $c = array('a'))
	{
	}

	function &c(Exception &$d = null)
	{
	}
}