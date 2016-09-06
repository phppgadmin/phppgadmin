<?php
namespace PHPPgAdmin\Database;
/**
 * PostgreSQL 9.2 support
 *
 */

class Postgres92 extends Postgres93 {

	var $major_version = 9.2;

	// Help functions

	function getHelpPages() {
		include_once './help/PostgresDoc92.php';
		return $this->help_page;
	}

}
