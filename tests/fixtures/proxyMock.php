<?php

namespace Mockista\Test;

class ProxiedObject
{

	private $constructorArgs;

	public function __construct()
	{
		$this->constructorArgs = func_get_args();
	}

	public function add($a, $b)
	{
		return $a + $b;
	}

	public function getConstructorArgs()
	{
		return $this->constructorArgs;
	}

	public function addToTwo($a)
	{
		return $a + $this->getTwo();
	}

	public function getTwo()
	{
		return 2;
	}

}