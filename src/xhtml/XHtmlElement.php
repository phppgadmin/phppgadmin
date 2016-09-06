<?php

namespace PHPPgAdmin\XHtml;

/**
 *  XHtmlElement
 *
 *  Used to generate Xhtml-Code for xhtml elements
 *  that can contain child elements
 *
 *
 */
class XHtmlElement extends XHtmlSimpleElement {
	var $_text = null;
	var $_htmlcode = "";
	var $_siblings = array();

	function __construct($text = null) {

		parent::__construct();

		if ($text) {
			$this->set_text($text);
		}

	}

	/*
		* Adds an xhtml child to element
		*
		* @param	XHtmlElement 	The element to become a child of element
	*/
	function add(&$object) {
		array_push($this->_siblings, $object);
	}

	/*
		* The CDATA section of Element
		*
		* @param	string	Text
	*/
	function set_text($text) {
		if ($text) {
			$this->_text = htmlspecialchars($text);
		}

	}

	function fetch() {
		return $this->_html();
	}

	function _html() {

		$this->_htmlcode = "<{$this->_element}";
		foreach ($this->_attributes as $attribute => $value) {
			if (!empty($value)) {
				$this->_htmlcode .= " {$attribute} =\"{$value}\"";
			}

		}
		$this->_htmlcode .= ">";

		if ($this->_text) {
			$this->_htmlcode .= $this->_text;
		}

		foreach ($this->_siblings as $obj) {
			$this->_htmlcode .= $obj->fetch();
		}

		$this->_htmlcode .= "</{$this->_element}>";

		return $this->_htmlcode;
	}

	/*
		* Returns siblings of Element
		*
	*/
	function get_siblings() {
		return $this->_siblings;
	}

	function has_siblings() {
		return (count($this->_siblings) != 0);
	}
}