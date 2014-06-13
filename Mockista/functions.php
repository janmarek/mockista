<?php

namespace Mockista;

function mock($mocked = NULL, array $defaults = array())
{
	$builder = new MockBuilder($mocked, $defaults);

	return $builder->getMock();
}