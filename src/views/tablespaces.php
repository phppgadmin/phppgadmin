<?php

/**
 * Manage tablespaces in a database cluster
 *
 * $Id: tablespaces.php,v 1.16 2007/08/31 18:30:11 ioguix Exp $
 */

// Include application functions
require_once '../lib.inc.php';

$tablespaces_controller = new \PHPPgAdmin\Controller\TableSpacesController($container);

$tablespaces_controller->render();
