<?php

$cli->command('init', 'Initialize ClearMarkup Starter Kit', function () {
    $this->printBox("Welcome to ClearMarkup Starter Kit", 'white', 'blue');

    $project_name = $this->ask("Name your project:");

    $project_url = $this->ask("Type the URL of your project:", 'http://localhost');

    $database_type = $this->ask("Type the database type:", 'mysql');

    $database = [];

    switch ($database_type) {
        case 'mysql':
        case 'pgsql':
            $database_host = $this->ask("Type the database host:", '127.0.0.1');
            $database_port = $this->ask("Type the database port:", '3306');
            $database_name = $this->ask("Type the database name:", strtolower(str_replace(' ', '_', $project_name)));
            $database_username = $this->ask("Type the database username:", 'root');
            $database_password = $this->ask("Type the database password:", '');

            try {
                $database_host_pdo = $database_host === 'localhost' ? '127.0.0.1' : $database_host;
                $pdo = new PDO("$database_type:host=$database_host_pdo;port=$database_port", $database_username, $database_password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS $database_name");

                if ($database_type === 'mysql') {
                    $pdo->exec("USE $database_name");
                    $pdo->exec(file_get_contents(__DIR__ . '/../database/MySQL.sql'));
                } else {
                    $pdo->exec("ALTER DATABASE $database_name SET search_path TO public");
                    $pdo->exec(file_get_contents(__DIR__ . '/../database/PostgreSQL.sql'));
                }
            } catch (PDOException $e) {
                $this->printError($e->getMessage());
                exit;
            }

            $database = [
                'type' => $database_type,
                'host' => $database_host,
                'port' => $database_port,
                'database' => $database_name,
                'username' => $database_username,
                'password' => $database_password,
                'charset' => 'utf8mb3',
                'collation' => 'utf8mb3_general_ci',
            ];

            break;
        case 'sqlite':
            $database_file = $this->ask("Type the database file:", 'database.sqlite');

            try {
                $pdo = new PDO("sqlite:" . PROJECT_ROOT . $database_file);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdo->exec(file_get_contents(__DIR__ . '/../database/SQLite.sql'));
            } catch (PDOException $e) {
                $this->printError($e->getMessage());
                exit;
            }

            $database = [
                'type' => 'sqlite',
                'database' => '__DIR__ . \'/\' . \'' . $database_file . '\'',
            ];
            break;
    }

    function arrayToString($array)
    {
        $result = "[\n";
        foreach ($array as $key => $value) {
            if (strpos($value, '__DIR__') === 0) {
                $result .= "        '" . $key . "' => " . $value . ",\n";
            } else {
                $result .= "        '" . $key . "' => '" . $value . "',\n";
            }
        }
        $result .= "    ]";
        return $result;
    }

    file_put_contents(PROJECT_ROOT . 'config.php', "<?php
/**
 * ClearMarkup Configuration
 * 
 * This file contains the configuration for the ClearMarkup application.
 * 
 * @package ClearMarkup
 * 
 */

\$config = (object) [
    'sitename' => '$project_name',
    'url' => '$project_url',
    'root' => __DIR__ . '/',
    'version' => '0.1.0',
    'locale' => 'en_US',
    'debug' => true,
    'openssl_key' => '',
    'session_name' => 'ClearMarkup',
    'database' => " . arrayToString($database) . ",
    'password_policy' => [
        'length' => 8,
        'uppercase' => 1,
        'lowercase' => 1,
        'digit' => 1,
        'special' => 1
    ],
    'remember_duration' => (int) (60 * 60 * 24 * 365.25 / 12),
    'smtp' => [
        'host' => 'localhost',
        'SMTPAuth' => false,
        'username' => 'mail@localhost',
        'password' => '',
        'SMTPSecure' => false,
        'port' => 2500
    ],
    'mail_from' => '',
    'mail_from_text' => ''
];");

    $buildFiles = [
        "controller/",
        "routes/",
        "locales/",
        "public/",
        "views/",
        "index.php",
        "config.php",
    ];

    if ($database_type === 'sqlite') {
        $buildFiles[] = $database_file;
    }


    file_put_contents(PROJECT_ROOT . 'ClearMarkup.json', json_encode([
        "buildFiles" => $buildFiles
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $this->print("✅ ClearMarkup Starter Kit initialized successfully", 'green');
});
