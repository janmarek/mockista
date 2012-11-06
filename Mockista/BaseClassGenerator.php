<?php

namespace Mockista;

abstract class BaseClassGenerator
{

	protected $methodFinder;

	public function setMethodFinder($methodFinder)
	{
		return $this->methodFinder = $methodFinder;
	}

	protected function generateParams($params)
	{
		$out = array();

		foreach ($params as $param) {
			if ($param['typehint']) {
				$outArr = $param['typehint'] . ' ';
			} else {
				$outArr = '';
			}

			if ($param['passedByReference']) {
				$outArr .= '&';
			}

			$outArr .= '$' . $param['name'];
			if (array_key_exists('default', $param)) {
				$outArr .= ' = ' . $this->removeNewLines(
					var_export($param['default'], true)
				);
			}
			$out[] = $outArr;
		}

		return join(', ', $out);
	}

	protected function removeNewLines($str)
	{
		return str_replace("\n", "", $str);
	}


	protected function containsNamespace($str)
	{
		return false !== strpos($str, "\\");
	}

	protected function removeNameSpace($str)
	{
		return substr($str, strrpos($str, "\\") + 1);
	}

	protected function namespaceCheck($out, $inheritedClass)
	{
		if ($this->containsNamespace($inheritedClass)) {
			$out .= "use {$inheritedClass};\n\n";
			$inheritedClass = $this->removeNameSpace($inheritedClass);
		}

		return array($out, $inheritedClass);
	}

}