<?php

namespace Mockista;

require_once __DIR__ . "/Expect.php";

function mock()
{
	return call_user_func_array(array("Mockista\MockFactory", "create"), func_get_args());
}