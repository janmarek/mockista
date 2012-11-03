<?php

namespace Mockista;

class ClassGeneratorException extends \RuntimeException
{
	const CODE_FINAL_CLASS_CANNOT_BE_MOCKED = 1;
}