<?php

$cli->command('class:add', 'Add a class', function () {
    if (!isset($this->argv[2]) && empty($this->argv[2])) {
        $this->printError('Please provide a class name');
        exit;
    }

    $class_name = ucfirst($this->argv[2]);

    if (!is_dir(PROJECT_ROOT . 'controller')) {
        mkdir(PROJECT_ROOT . 'controller', 0777, true);
    }

    file_put_contents(PROJECT_ROOT . 'controller/' . $class_name . '.php', "<?php
namespace ClearMarkup\\Classes;
class $class_name
{
    public function __construct()
    {
        
    }
}");

    $this->print("$class_name has been created.", 'green');
}, 'ca');

$cli->command('class:extends', 'Extend a class', function () {
    if (!isset($this->argv[2]) && empty($this->argv[2])) {
        $this->printError('Please provide a class name');
        exit;
    }

    $class_name = ucfirst($this->argv[2]);

    if (!is_dir(PROJECT_ROOT . 'controller/extends')) {
        mkdir(PROJECT_ROOT . 'controller/extends', 0777, true);
    }

    file_put_contents(PROJECT_ROOT . 'controller/extends/Extended' . $class_name . '.php', "<?php
namespace ClearMarkup\\Classes\\extends;
use ClearMarkup\\Classes\\$class_name;

class Extended$class_name extends $class_name
{
    public function __construct()
    {
        parent::__construct();

        
    }
}");

    $this->print("Extended$class_name has been created and extends $class_name.", 'green');

    if (isset($this->argv[3]) && $this->argv[3] == '-r' || isset($this->argv[3]) && $this->argv[3] == '-replace') {

        $directory = new RecursiveDirectoryIterator(PROJECT_ROOT . 'routes');
        $iterator = new RecursiveIteratorIterator($directory);

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $file_content = file_get_contents($file->getPathname());

                $pattern = '/new\s+' . $class_name . '(\(|\;)/';
                $replacement = 'new Extended' . $class_name . '$1';

                $file_content = preg_replace($pattern, $replacement, $file_content);

                $patternUse = '/use ClearMarkup\\\Classes\\\\' . $class_name . ';/';
                $replacementUse = 'use ClearMarkup\\\Classes\\\\Extends\\\\' . 'Extended' . $class_name . ';';

                $file_content = preg_replace($patternUse, $replacementUse, $file_content);

                file_put_contents($file->getPathname(), $file_content);
            }
        }
        
        $this->print("All instances of $class_name and 'use ClearMarkup\\Classes\\$class_name;' have been replaced with Extended$class_name and 'use ClearMarkupValidation\Extends\$class_name;'.", 'green');
    }

}, 'ce');
