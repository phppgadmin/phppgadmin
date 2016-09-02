<?php

/**
 * Common relation browsing function that can be used for views,
 * tables, reports, arbitrary queries, etc. to avoid code duplication.
 * @param $query The SQL SELECT string to execute
 * @param $count The same SQL query, but only retrieves the count of the rows (AS total)
 * @param $return The return section
 * @param $page The current page
 *
 * $Id: display.php,v 1.68 2008/04/14 12:44:27 ioguix Exp $
 */

// Include application functions
if (!defined('BASE_PATH')) {
	require_once '../lib.inc.php';
}

$display_controller = new \PHPPgAdmin\Controller\DisplayController($container);

$display_controller->render();
