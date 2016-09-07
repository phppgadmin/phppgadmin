<?php

/**
 * Does an export to the screen or as a download.  This checks to
 * see if they have pg_dump set up, and will use it if possible.
 *
 * $Id: dataexport.php,v 1.26 2007/07/12 19:26:22 xzilla Exp $
 */

require_once '../lib.inc.php';

$dataexport_controller = new \PHPPgAdmin\Controller\DataExportController($container);

$dataexport_controller->render();