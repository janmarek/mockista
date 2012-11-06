<?php

namespace Mockista;

function mock($class = NULL, array $defaults = array())
{
	$builder = new MockBuilder($class, $defaults);

	return $builder->getMock();
}