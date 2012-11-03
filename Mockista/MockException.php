<?php

namespace Mockista;

if (class_exists("\PHPUnit_Framework_AssertionFailedError")) {
	class MockException extends \PHPUnit_Framework_AssertionFailedError
	{
		const CODE_EXACTLY = 1;
		const CODE_AT_LEAST = 2;
		const CODE_NO_MORE_THAN = 3;
		const CODE_INVALID_ARGS = 4;
	}
} else {
	class MockException extends \Exception
	{
		const CODE_EXACTLY = 1;
		const CODE_AT_LEAST = 2;
		const CODE_NO_MORE_THAN = 3;
		const CODE_INVALID_ARGS = 4;
	}
}
