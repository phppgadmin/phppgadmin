<?php
namespace PHPPgAdmin\Database;
/**
 * PostgreSQL 9.3 support
 *
 */

class Postgres93 extends Postgres {

	var $major_version = 9.3;

	// Help functions

	function getHelpPages() {
		include_once './help/PostgresDoc93.php';
		return $this->help_page;
	}

}
