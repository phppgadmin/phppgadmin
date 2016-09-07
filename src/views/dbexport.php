<?php
/**
 * Does an export of a database, schema, or table (via pg_dump)
 * to the screen or as a download.
 *
 * $Id: dbexport.php,v 1.22 2007/03/25 03:15:09 xzilla Exp $
 */

require_once '../lib.inc.php';

$dbexport_controller = new \PHPPgAdmin\Controller\DBExportController($container);

$dbexport_controller->render();