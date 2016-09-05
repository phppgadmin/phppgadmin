<?php

/**
 * Manage schemas in a database
 *
 * $Id: schemas.php,v 1.22 2007/12/15 22:57:43 ioguix Exp $
 */

// Include application functions
require_once '../lib.inc.php';

$schema_controller = new \PHPPgAdmin\Controller\SchemaController($container);

$schema_controller->render();
