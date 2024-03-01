<?php

$cli->command('class', 'Greet someone', function() {
    if ($this->argc < 3) {
        $this->help('greet');
        return;
    }

    $this->print("Hello, " . $this->argv[2] . "!", 'green', 'white');
});