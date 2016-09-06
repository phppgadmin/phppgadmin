<?php
namespace PHPPgAdmin\Decorators;

class FieldDecorator extends Decorator {
	function __construct($fieldName, $default = null) {
		$this->f = $fieldName;
		if ($default !== null) {
			$this->d = $default;
		}

	}

	function value($fields) {
		return isset($fields[$this->f]) ? Decorator::get_sanitized_value($fields[$this->f], $fields) : (isset($this->d) ? $this->d : null);
	}

}