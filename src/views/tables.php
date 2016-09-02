<?php

/**
 * List tables in a database
 *
 * $Id: tables.php,v 1.112 2008/06/16 22:38:46 ioguix Exp $
 */

// Include application functions
require_once '../lib.inc.php';

$table_controller = new \PHPPgAdmin\Controller\TableController($container);
$table_controller->render();