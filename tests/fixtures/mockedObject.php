<?php

class MockedObject {

	private $state;

	public function __construct($state) {
		$this->state = $state;
	}

	public function getOne() {
		return 1;
	}

	public function timesTwo($value) {
		return $value * 2;
	}

	public function getState() {
		return $this->state;
	}

	public function setState($value) {
		$this->state = $value;
	}

}
 