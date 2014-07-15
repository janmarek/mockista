## Installation [![Build Status](https://secure.travis-ci.org/janmarek/mockista.png?branch=master)](http://travis-ci.org/janmarek/mockista)

Install via composer:

    $ composer require --dev janmarek/mockista

It is recommended to create base test class with mockista functionality:

```php
<?php
abstract class BaseTestCase extends \PHPUnit_Framework_TestCase
{

	/** @var \Mockista\Registry */
	protected $mockista;

	protected function setUp()
	{
		$this->mockista = new \Mockista\Registry();
	}

	protected function tearDown()
	{
		$this->mockista->assertExpectations();
	}

}

```

## Quick Start

Basic syntax:

```php
<?php
class SomeTestCase extends BaseTestCase
{

	private $mock1;

	private $mock2;

	protected function setUp()
	{
		parent::setUp();

		$this->mock1 = $this->mockista->create();
		$this->mock1->expects('method')->andReturn(5);
		$this->mock1->expects('method')->once()->with(1, 2, 3)->andReturn(4);

		// or you can use mock builder with nicer syntax
		$builder = $this->mockista->createBuilder();
		$builder->method()->andReturn(5);
		$builder->method(1, 2, 3)->once->andReturn(4);
		$this->mock2 = $builder->getMock();

		// you can create mock of existing class
		$this->mock3 = $this->mockista->create('ExistingClass', array(
			'abc' => 1,              // you can define return values easily
			'def' => function ($a) {
				return $a * 2;
			}
		));
	}

	public function testMock1()
	{
		$this->assertEquals(5, $this->mock1->method());
		$this->assertEquals(5, $this->mock1->method('abc'));
		$this->assertEquals(4, $this->mock1->method(1, 2, 3));
	}

	public function testMock2()
	{
		$this->assertEquals(5, $this->mock1->method());
		$this->assertEquals(5, $this->mock1->method('abc'));
		$this->assertEquals(4, $this->mock1->method(1, 2, 3));
	}

	public function testMock3()
	{
		$this->assertEquals(1, $this->mock1->abc());
		$this->assertEquals(4, $this->mock1->def(2));
	}

}

```

### Parameter matching
   
Parameters can be matched by value:

```php
$mock->expects('method')->once()->with(1, 'abc', TRUE)->andReturn(4);
$builder->method(1, 'abc', TRUE)->andReturn(4);
```

Or you can use smarter parameter matcher:

```php
$mock->expects('method')->once()->with(Matchers::isInt(), Matchers::isString(), Matchers::isBool())->andReturn(4);
$builder->method(Matchers::isInt(), Matchers::isString(), Matchers::isBool())->andReturn(4);
```

Available matchers are:

* Matchers::isBool()
* Matchers::isNumeric()
* Matchers::isInt()
* Matchers::isFloat()
* Matchers::isString()
* Matchers::isArray()
* Matchers::regexp($pattern) - check string parameter by regular expression
* Matchers::callback($callback) - check parameter by your custom logic passed in a callback