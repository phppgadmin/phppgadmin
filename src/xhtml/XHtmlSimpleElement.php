<?php

namespace PHPPgAdmin\XHtml;

/**
 *  XHtmlSimpleElement
 *
 *  Used to generate Xhtml-Code for simple xhtml elements
 *  (i.e. elements, that can't contain child elements)
 *
 *
 *  @author	Felix Meinhold
 *
 */
class XHtmlSimpleElement {
	var $_element;
	var $_siblings = array();
	var $_htmlcode;
	var $_attributes = array();

	/**
	 * Constructor
	 *
	 * @param	string	The element's name. Defaults to name of the
	 * derived class
	 *
	 */
	function __construct($element = null) {

		$this->_element = $this->is_element();

	}

	function set_style($style) {
		$this->set_attribute('style', $style);
	}

	function set_class($class) {
		$this->set_attribute('class', $class);
	}

	function is_element() {
		return
		str_replace('phppgadmin\xhtml\xhtml_', '', strtolower(get_class($this)));
	}

	/**
	 * Private function generates xhtml
	 * @access private
	 */
	function _html() {
		$this->_htmlcode = "<";
		foreach ($this->_attributeCollection as $attribute => $value) {
			if (!empty($value)) {
				$this->_htmlcode .= " {$attribute}=\"{$value}\"";
			}

		}
		$this->_htmlcode .= "/>";

		return $this->_htmlcode;
	}

	/**
	 * Returns xhtml code
	 *
	 */
	function fetch() {
		return $this->_html();
	}
	/**
	 * Echoes xhtml
	 *
	 */
	function show() {
		echo $this->fetch();
	}

	function set_attribute($attr, $value) {
		$this->_attributes[$attr] = $value;
	}

}