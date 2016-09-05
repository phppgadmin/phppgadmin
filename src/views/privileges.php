<?php

/**
 * Manage privileges in a database
 *
 * $Id: privileges.php,v 1.45 2007/09/13 13:41:01 ioguix Exp $
 */

require_once '../lib.inc.php';

$privilege_controller = new \PHPPgAdmin\Controller\PrivilegeController($container);

$privilege_controller->render();