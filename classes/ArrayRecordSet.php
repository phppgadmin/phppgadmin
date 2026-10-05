<?php

/**
 * Really simple RecordSet to allow printTable of arrays.
 *
 * $Id: ArrayRecordSet.php,v 1.3 2007/01/10 01:46:28 soranzo Exp $
 */
class ArrayRecordSet {

	private $_array;
	private $_count;
	public $EOF = false;
	public $fields;
	
	function __construct($data) {
		$this->_array = $data;
		$this->_count = count($this->_array);
		$this->fields = reset($this->_array);
		if ($this->fields === false) $this->EOF = true;
	}
	
	function recordCount() {
		return $this->_count;
	}
	
	function moveNext() {
		$this->fields = next($this->_array);
		if ($this->fields === false) $this->EOF = true;
	}
}

?>
