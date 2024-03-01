<?php

$cli->command('server:mail', 'Start the mail server', function () {
    shell_exec('cd ' . __DIR__ . '/../mailslurper && ./mailslurper');
}, 'sm');

$cli->command('server:web', 'Start the web server', function () {
    $port = $argv[2] ?? '8000';
    $errorLogPath = PROJECT_ROOT . 'errors.txt';
    shell_exec("php -S 0.0.0.0:$port -t " . PROJECT_ROOT . " " . __DIR__ ."/../start.php -d error_log=$errorLogPath -d log_errors=On");
}, 'sw');
