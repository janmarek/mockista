<?php

namespace Mockista;

interface MethodInterface
{
	public function once();
	public function twice();
	public function never();

	public function exactly($count);

	public function atLeastOnce();
	public function atLeast($count);

	public function noMoreThan($count);

	public function andReturn($returnValue);

	public function andThrow($throwException);

	public function andCallback($callback);

	public function invoke($args);
}
