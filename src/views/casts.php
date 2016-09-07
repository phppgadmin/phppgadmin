<?php

/**
 * Manage casts in a database
 *
 * $Id: casts.php,v 1.16 2007/09/25 16:08:05 ioguix Exp $
 */

// Include application functions
require_once '../lib.inc.php';

$cast_controller = new \PHPPgAdmin\Controller\CastController($container);

$cast_controller->render();