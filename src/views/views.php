<?php

/**
 * Manage views in a database
 *
 * $Id: views.php,v 1.75 2007/12/15 22:57:43 ioguix Exp $
 */

// Include application functions
require_once '../lib.inc.php';

$view_controller = new \PHPPgAdmin\Controller\ViewController($container);

$view_controller->render();