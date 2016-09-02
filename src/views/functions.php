<?php

/**
 * Manage functions in a database
 *
 * $Id: functions.php,v 1.78 2008/01/08 22:50:29 xzilla Exp $
 */

// Include application functions
require_once '../lib.inc.php';

$function_controller = new \PHPPgAdmin\Controller\FunctionController($container);

$function_controller->render();
