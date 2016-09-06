<?php

namespace PHPPgAdmin\XHtml;

class XHTML_Button extends XHtmlElement {

	function __construct($name, $text = null) {

		parent::__construct();

		$this->set_attribute("name", $name);

		if ($text) {
			$this->set_text($text);
		}

	}
}
