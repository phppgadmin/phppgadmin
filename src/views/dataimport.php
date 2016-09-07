<?php

/**
 * Does an import to a particular table from a text file
 *
 * $Id: dataimport.php,v 1.11 2007/01/22 16:33:01 soranzo Exp $
 */

require_once '../lib.inc.php';

$dataimport_controller = new \PHPPgAdmin\Controller\DataImportController($container);

$dataimport_controller->render();