<?php
/**
 * Class MockTest
 *
 * @author: Jiří Šifalda <sifalda.jiri@gmail.com>
 * @date: 24.06.13
 */
namespace Mockista\Test;

use Mockista\Registry;

class FakeDependency {

	public function testMe(array $args)
	{
		return $args;
	}
}

class TestMe {

	private $fake;

	public function __construct(FakeDependency $fake)
	{
		$this->fake = $fake;
	}

	public function testMe()
	{
		return $this->fake->testMe(array('args'));
	}
}

class MockTest extends \PHPUnit_Framework_TestCase
{

	/** @var  Registry */
	private $mockista;

	/** @var  TestMe */
	private $testClass;

	/** @var  \Mockista\MockInterface */
	private $fakeDependency;

	public function setUp()
	{
		$this->mockista = new Registry;

		$this->fakeDependency = $this->mockista->create('\Mockista\Test\FakeDependency');
		$this->testClass = new TestMe($this->fakeDependency);
	}

	public function testArgsAssertion()
	{

		$this->fakeDependency->expects('testMe')
			->once()
			->with(array('args'))
			->andReturn(array('args'));

		$this->assertSame(array('args'), $this->testClass->testMe());
	}

	public function tearDown()
	{
		$this->mockista->assertExpectations();
	}
}