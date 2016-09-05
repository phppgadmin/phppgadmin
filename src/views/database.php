<?php

/**
 * Manage schemas within a database
 *
 * $Id: database.php,v 1.104 2007/11/30 06:04:43 xzilla Exp $
 */

// Include application functions
require_once '../lib.inc.php';

$database_controller = new \PHPPgAdmin\Controller\DatabaseController($container);

$database_controller->render();
