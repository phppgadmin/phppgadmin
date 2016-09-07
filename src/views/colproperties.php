<?php

/**
 * List Columns properties in tables
 *
 * $Id: colproperties.php
 */

// Include application functions
require_once '../lib.inc.php';

$colproperty_controller = new \PHPPgAdmin\Controller\ColPropertyController($container);

$colproperty_controller->render();
