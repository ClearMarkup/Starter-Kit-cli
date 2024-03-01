<?php

$cli->command('routes:addapi', 'Add a new API route', function () {

    if (!isset($this->argv[2])) {
        $this->printError('Please specify a method');
        return;
    }

    if (!isset($this->argv[3])) {
        $this->printError('Please specify a route name');
        return;
    }

    if (!file_exists(PROJECT_ROOT . 'routes/api')) {
        mkdir(PROJECT_ROOT . 'routes/api');
    }

    $dir = PROJECT_ROOT . 'routes/api/';

    $method = strtoupper($this->argv[2]);
    $route_name = str_replace(' ', '_', $this->argv[3]);

    $url = $route_name;
    if (substr($url, 0, 1) !== '/') {
        $url = '/' . $url;
    }

    $file_name = $route_name;
    if (strpos($file_name, '/') !== false) {
        $file_name = substr($file_name, strrpos($file_name, '/') + 1);
    }

    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }

    file_put_contents($dir . $file_name . '.api.php', "<?php
use ClearMarkup\\Classes\\Api;

\$router->map('$method', '$url', function () {
    \$api = new Api;
    

});");
}, 'ra');
