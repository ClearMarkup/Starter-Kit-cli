<?php
define('PROJECT_ROOT', getcwd() . '/');

require_once 'CLI.php';

$cli = new CLI();

foreach (glob(__DIR__ . "/commands/*.php") as $filename) {
    require_once $filename;
}

$cli->run();