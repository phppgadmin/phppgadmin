<?php

/**
 * PostgreSQL 9.2 support
 *
 */

include_once('./classes/database/Postgres.php');

class Postgres92 extends Postgres {

	var $major_version = 9.2;

	/**
	 * Constructor
	 * @param $conn The database connection
	 */
	function Postgres92($conn) {
		$this->Postgres($conn);
	}

	// Help functions

	function getHelpPages() {
		include_once('./help/PostgresDoc92.php');
		return $this->help_page;
	}

}
?>
