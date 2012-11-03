<?php

namespace Mockista;

interface MockInterface
{
	public function freeze();
	public function assertExpectations();
}