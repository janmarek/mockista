<?php

namespace Mockista;

interface MethodInterface
{

	/**
	 * @return MethodInterface
	 */
	public function with();

	/**
	 * @return MethodInterface
	 */
	public function once();

	/**
	 * @return MethodInterface
	 */
	public function twice();

	/**
	 * @return MethodInterface
	 */
	public function never();

	/**
	 * @param int $count
	 * @return MethodInterface
	 */
	public function exactly($count);

	/**
	 * @return MethodInterface
	 */
	public function atLeastOnce();

	/**
	 * @param int $count
	 * @return MethodInterface
	 */
	public function atLeast($count);

	/**
	 * @param int $count
	 * @return MethodInterface
	 */
	public function noMoreThan($count);

	/**
	 * @param mixed $returnValue
	 * @return MethodInterface
	 */
	public function andReturn($returnValue);

	/**
	 * @param \Exception $throwException
	 * @return MethodInterface
	 */
	public function andThrow($throwException);

	/**
	 * @param \Closure $callback
	 * @return MethodInterface
	 */
	public function andCallback($callback);

	public function invoke($args);

	public function assertExpectations();

}
