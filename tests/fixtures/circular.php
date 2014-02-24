<?php

namespace Mockista\Test;

class Circular implements \Serializable
{

	private $reference;

	public function __construct()
	{
		$this->reference = $this;
	}

	public function serialize()
	{
		throw new \Exception();
	}

	public function unserialize($serialized)
	{

	}

}