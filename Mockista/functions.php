<?php

namespace Mockista;

function mock()
{
	return call_user_func_array(array("Mockista\MockFactory", "create"), func_get_args());
}