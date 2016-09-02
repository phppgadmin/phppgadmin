<?php

/**
 * Manage roles in a database cluster
 *
 * $Id: roles.php,v 1.13 2008/03/21 15:32:57 xzilla Exp $
 */

// Include application functions
require_once '../lib.inc.php';

$roles_controller = new \PHPPgAdmin\Controller\RolesController($container);

$roles_controller->render();
