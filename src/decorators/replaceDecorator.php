<?php
namespace PHPPgAdmin\Decorators;

class replaceDecorator extends Decorator {
	function __construct($str, $params) {
		$this->s = $str;
		$this->p = $params;
	}

	function value($fields) {
		$str = $this->s;
		foreach ($this->p as $k => $v) {
			$str = str_replace($k, Decorator::get_sanitized_value($v, $fields), $str);
		}
		return $str;
	}
}