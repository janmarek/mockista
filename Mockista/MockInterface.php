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

	/**
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	public function callOriginalMethod($name, $args);

}