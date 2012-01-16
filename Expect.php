<?php

namespace Mockista;

function I_expect($value = null)
{
	return new Expectation($value);
}

class Expectation
{
}
