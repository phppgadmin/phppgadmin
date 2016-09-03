<?php

if (!defined('BASE_PATH')) {
	$_no_db_connection = true;
	require_once '../lib.inc.php';
}

$intro_controller = new \PHPPgAdmin\Controller\IntroController($container);

$intro_controller->render();
