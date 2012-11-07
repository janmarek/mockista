<?php

namespace Mockista;

interface MockInterface
{

	/**
	 * @param string $methodName
	 * @return MethodInterface
	 */
	public function expects($methodName);

	public function assertExpectations();

}