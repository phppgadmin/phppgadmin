<?php
namespace PHPPgAdmin\Decorators;

class ActionUrlDecorator extends Decorator {
	function __construct($base, $queryVars = null) {

		$this->b = $base;
		if ($queryVars !== null) {
			$this->q = $queryVars;
		}

	}

	function value($fields) {
		$url = Decorator::get_sanitized_value($this->b, $fields);

		if ($url === false) {
			return '';
		}

		if (!empty($this->q)) {
			$queryVars = Decorator::get_sanitized_value($this->q, $fields);

			$sep = '?';
			foreach ($queryVars as $var => $value) {
				$url .= $sep . Decorator::value_url($var, $fields) . '=' . Decorator::value_url($value, $fields);
				$sep = '&';
			}
		}
		return '/src/views/' . $url;
	}
}