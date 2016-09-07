<?php

/**
 * Login screen
 *
 * $Id: login.php,v 1.38 2007/09/04 19:39:48 ioguix Exp $
 */
if (!defined('BASE_PATH')) {

	require_once '../lib.inc.php';
}

$login_controller = new \PHPPgAdmin\Controller\LoginController($container);

$login_controller->render();