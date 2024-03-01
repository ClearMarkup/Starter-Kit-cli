<?php
require_once __DIR__ . '/config.php';

require_once 'CLI.php';

$cli = new CLI();

foreach (glob(__DIR__ . "/commands/*.php") as $filename) {
    require_once $filename;
}

$cli->run();