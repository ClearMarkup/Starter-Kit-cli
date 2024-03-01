<?php

$cli->command('build', 'Build the project', function () {
    if (!file_exists(PROJECT_ROOT . 'build')) {
        mkdir(PROJECT_ROOT . 'build');
    } else {
        shell_exec('rm -rf ' . PROJECT_ROOT . 'build');
        mkdir(PROJECT_ROOT . 'build');
    }

    try {
        if (file_exists(PROJECT_ROOT . 'ClearMarkup.json')) {
            $config = json_decode(file_get_contents(PROJECT_ROOT . 'ClearMarkup.json'), true);
        } else {
            throw new Exception('ClearMarkup.json does not exist');
        }
    } catch (Exception $e) {
        $this->printError($e->getMessage());
        exit;
    }

    if (isset($this->argv[2]) && $this->argv[2] === '-pwd') {
        if (!isset($this->argv[3]) || !isset($this->argv[4])) {
            $username = $this->ask("Please enter a username:");
            $password = $this->ask("Please enter a password:");
        } else {
            $username = $this->argv[3];
            $password = $this->argv[4];
        }

        $htpasswdPath = PROJECT_ROOT . 'build/' . '.htpasswd';

        if (!file_exists($htpasswdPath)) {
            shell_exec('touch ' . $htpasswdPath);
        }

        shell_exec('htpasswd -b ' . $htpasswdPath . ' ' . $username . ' ' . $password);

        $htaccessPath = PROJECT_ROOT . 'build/public/.htaccess';
        $htaccessContent = file_get_contents($htaccessPath);
        $htaccessContent = str_replace('# HTPASSWD', '
    AuthType Basic
    AuthName "Restricted Area"
    AuthUserFile ../.htpasswd
    Require valid-user
    ', $htaccessContent);
        file_put_contents($htaccessPath, $htaccessContent);
    }

    $build_files = $config['buildFiles'];

    foreach ($build_files as $file) {
        $filePath = PROJECT_ROOT . $file;

        if (file_exists($filePath)) {
            if (substr($file, -2) === '/!') {
                shell_exec('rsync -a -f"+ */" -f"- *" ' . $filePath . ' ' . PROJECT_ROOT . 'build/');
                continue;
            } else if (is_dir($filePath)) {
                shell_exec('rsync -a ' . $filePath . ' ' . PROJECT_ROOT . 'build/' . $file);
            } else {
                copy($filePath, PROJECT_ROOT . 'build/' . $file);
            }
            $this->print("Copied: $file", 'green');
        } else {
            $this->print("File or directory does not exist: $filePath", 'yellow');
        }
    }

    // Copy composer.json to build directory
    copy(PROJECT_ROOT . 'composer.json', PROJECT_ROOT . 'build/composer.json');
    copy(PROJECT_ROOT . 'composer.lock', PROJECT_ROOT . 'build/composer.lock');

    // Install non-dev dependencies in build directory
    shell_exec('cd ' . PROJECT_ROOT . 'build && composer install --no-dev');

    // Remove composer.json from build directory
    unlink(PROJECT_ROOT . 'build/composer.json');
    unlink(PROJECT_ROOT . 'build/composer.lock');

    $this->print("✅ Build complete! You can find the build files in the build/ directory", 'green');
}, 'b');
