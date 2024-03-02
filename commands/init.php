<?php

$cli->command('init', 'Initialize ClearMarkup Starter Kit', function () {
    $this->printBox("Welcome to ClearMarkup Starter Kit", 'white', 'blue');

    $project_name = $this->ask("Name your project:");

    $project_url = $this->ask("Type the URL of your project:", 'http://localhost');

    $database_type = $this->ask("Type the database type:", 'mysql');

    $database = "";

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

            $database = trim("
                DB_TYPE=$database_type
                DB_HOST=$database_host
                DB_PORT=$database_port
                DB_DATABASE=$database_name
                DB_USERNAME=$database_username
                DB_PASSWORD=$database_password
            ");

            $database = implode("\n", array_map('trim', explode("\n", $database)));

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

            $database = trim("
                DB_TYPE=$database_type
                DB_DATABASE=$database_file
            ");

            $database = implode("\n", array_map('trim', explode("\n", $database)));
            break;
    }

    $output = trim("
        # App settings
        SITENAME=$project_name
        SITE_URL=$project_url
        VERSION=0.1.0
        LOCALE=en_US
        DEBUG=true
        OPENSSL_KEY=
        SESSION_NAME=ClearMarkup

        # Database settings
        $database

        # Security settings
        PASSWORD_POLICY_LENGTH=8
        PASSWORD_POLICY_UPPERCASE=1
        PASSWORD_POLICY_LOWERCASE=1
        PASSWORD_POLICY_DIGIT=1
        PASSWORD_POLICY_SPECIAL=1
        REMEMBER_DURATION=31557600

        # SMTP settings
        SMTP_HOST=localhost
        SMTP_AUTH=false
        SMTP_USERNAME=mail@localhost
        SMTP_PASSWORD=
        SMTP_SECURE=false
        SMTP_PORT=2500
        MAIL_FROM=
        MAIL_FROM_TEXT="
    );

    $output = implode("\n", array_map('trim', explode("\n", $output)));

    file_put_contents(PROJECT_ROOT . '.env', $output);

    $buildFiles = [
        "controller/",
        "routes/",
        "locales/",
        "public/",
        "views/",
        ".env",
    ];

    if ($database_type === 'sqlite') {
        $buildFiles[] = $database_file;
    }


    file_put_contents(PROJECT_ROOT . 'ClearMarkup.json', json_encode([
        "buildFiles" => $buildFiles
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $this->print("✅ ClearMarkup Starter Kit initialized successfully", 'green');
});
