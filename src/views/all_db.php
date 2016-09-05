<?php

/**
 * Manage databases within a server
 *
 * $Id: all_db.php,v 1.59 2007/10/17 21:40:19 ioguix Exp $
 */

// Include application functions
if (!defined('BASE_PATH')) {
	require_once '../lib.inc.php';
}

$all_db_controller = new \PHPPgAdmin\Controller\AllDBController($container);

$all_db_controller->render();
