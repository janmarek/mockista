<?php
if (class_exists("PHPUnit_Framework_TestCase") && ! class_exists("KDev_Test")) {

	/**
	 * Tests base class
	 *
	 * @author Jiri Knesl
	**/
	abstract class KDev_Test extends PHPUnit_Framework_TestCase
	{
		protected $object;
		private $__mocks = array();

		/**
		 * 
		 */
		protected function setUp()
		{
			$this->cleanup();
			$this->__mocks = array();
			$this->prepare();
		}

		function tearDown()
		{
			$this->assertMocks();
			$this->cleanup();
		}

		function assertMocks()
		{
			foreach ($this->__mocks as $mock) {
				$mock->assertExpectations();
			}
		}

		function prepare()
		{
		}

		function cleanup()
		{
		}

		function __get($name)
		{
			if (0 === strpos($name, "mock")) {
				$mock = $this->$name();
				$mock->freeze();
				$this->__mocks[] = $mock;
				return $mock;
			}
		}

	}
}
