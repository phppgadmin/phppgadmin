<?php
namespace PHPPgAdmin\Decorators;

class RedirectUrlDecorator extends Decorator {
	function __construct($base, $queryVars = null) {

		//\PC::debug($base, 'RedirectUrlDecorator');

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
				$varname  = Decorator::value_url($var, $fields);
				$varvalue = Decorator::value_url($value, $fields);
				if ($varname == 'subject') {
					$url = '/' . str_replace('.php', '/' . $varvalue, $url);
				}
				$url .= $sep . $varname . '=' . $varvalue;
				$sep = '&';
			}
		}
		return str_replace('.php', '', $url);
	}
}