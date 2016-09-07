<?php

/**
 * List tables in a database
 *
 * $Id: tblproperties.php,v 1.92 2008/01/19 13:46:15 ioguix Exp $
 */

// Include application functions
require_once '../lib.inc.php';

$tableproperty_controller = new \PHPPgAdmin\Controller\TablePropertyController($container);

$tableproperty_controller->render();