<?php

$cli->command('locales:add', 'Add a new locale', function () {

    if (!isset($this->argv[2])) {
        $this->printError('Please specify a locale');
        return;
    }

    if (!file_exists(PROJECT_ROOT . 'locales')) {
        mkdir(PROJECT_ROOT . 'locales');
    }

    if (!file_exists(PROJECT_ROOT . 'locales/' . $this->argv[2])) {
        mkdir(PROJECT_ROOT . 'locales/' . $this->argv[2]);
    }

    if (!file_exists(PROJECT_ROOT . 'locales/' . $this->argv[2] . '/LC_MESSAGES')) {
        mkdir(PROJECT_ROOT . 'locales/' . $this->argv[2] . '/LC_MESSAGES');
    }

    $directories = [PROJECT_ROOT . 'controller', PROJECT_ROOT . 'views'];
    $outputFile = PROJECT_ROOT . 'locales/' . $this->argv[2] . '/LC_MESSAGES/messages.po';

    $files = [];

    $contents = file_put_contents($outputFile, '');

    foreach ($directories as $directory) {
        try {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        } catch (Exception $e) {
            $this->printError($e->getMessage());
            return;
        }

        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') continue;
            $files[] = escapeshellarg($file->getPathname());
        }
    }

    if ($files) {
        $command = 'xgettext -o ' . escapeshellarg($outputFile) . ' ' . implode(' ', $files);
        shell_exec($command);
    }

    // Replace "CHARSET" with "UTF-8" in the .po file
    $contents = file_get_contents($outputFile);
    $contents = str_replace('CHARSET', 'UTF-8', $contents);
    $contents = str_replace('"Language: \\n"', '"Language: ' . $this->argv[2] . '\\n"', $contents);
    file_put_contents($outputFile, $contents);

    $this->print('Locale ' . $this->argv[2] . ' added', 'green');
}, 'la');

$cli->command('locales:search', 'Search for a string in the locales', function () {
    // loop through all .php files in the controller and views directories
    $directories = [PROJECT_ROOT . 'controller', PROJECT_ROOT . 'views'];
    $files = [];

    foreach ($directories as $directory) {
        try {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        } catch (Exception $e) {
            $this->printError($e->getMessage());
            return;
        }

        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') continue;
            $files[] = escapeshellarg($file->getPathname());
            $this->print('Searching: ' . $file->getPathname());
        }
    }

    // loop through all .po files in locales/ and update them
    $locales = glob(PROJECT_ROOT . 'locales/*', GLOB_ONLYDIR);
    foreach ($locales as $locale) {
        $tempFile = $locale . '/LC_MESSAGES/messages_temp.po';
        $outputFile = $locale . '/LC_MESSAGES/messages.po';
        $command = 'xgettext -o ' . escapeshellarg($tempFile) . ' ' . implode(' ', $files);
        shell_exec($command);
        $this->print('Updated: ' . $tempFile, 'green');

        // Replace "CHARSET" with "UTF-8" in the .po file
        $contents = file_get_contents($tempFile);
        $contents = str_replace('"Content-Type: text/plain; charset=CHARSET\\n"', '"Content-Type: text/plain; charset=UTF-8\\n"', $contents);
        $contents = str_replace('"Language: \\n"', '"Language: ' . basename($locale) . '\\n"', $contents);
        file_put_contents($tempFile, $contents);

        if (file_exists($outputFile)) {
            $mergeCommand = 'msgmerge --update --backup=none ' . escapeshellarg($outputFile) . ' ' . escapeshellarg($tempFile);
            shell_exec($mergeCommand);
            unlink($tempFile);
        } else {
            rename($tempFile, $outputFile);
        }
    }

    $this->print('Search complete', 'green');
    $this->print('Found ' . count($files) . ' strings in ' . count($locales) . ' locales', 'green');
}, 'ls');

$cli->command('locales:compile', 'Compile the locales', function () {
    // Create .mo files from all .po files in locales/
    $this->print('Compiling locales...');
    
    $locales = glob(PROJECT_ROOT . 'locales/*', GLOB_ONLYDIR);
    foreach ($locales as $locale) {
        $poFiles = glob($locale . '/LC_MESSAGES/*.po');
        foreach ($poFiles as $poFile) {
            $moFile = str_replace('.po', '.mo', $poFile);
            $command = 'msgfmt -o ' . escapeshellarg($moFile) . ' ' . escapeshellarg($poFile);
            shell_exec($command);
            $this->print('Compiled: ' . $moFile, 'green');
        }
    }

    if (count($locales) > 0) {
        $this->print('Locales compiled', 'green');
    } else {
        $this->print('No locales found', 'yellow');
    }
}, 'lc');
