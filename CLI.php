<?php

class CLI {
    private $commands = [];
    private $argv;
    private $argc;

    public function __construct() {
        $this->argv = $_SERVER['argv'];
        $this->argc = $_SERVER['argc'];
    }

    public function help($command = null) {
        if ($command) {
            echo $this->commands[$command]['help'] . PHP_EOL;
            return;
        }
    
        $this->print("Usage: " . $this->argv[0] . " <command> [options]");
        $this->print("Available commands:");
        printf("  \033[1;34m%-5s %-20s\033[0m %s\n", "-h", "help", "Show this help message");
        foreach ($this->commands as $command => $data) {
            $shortcut = isset($data['shortcut']) ? '-' . $data['shortcut'] : '';
            printf("  \033[1;34m%-5s %-20s\033[0m %s\n", $shortcut , $command, $data['help']);
        }
    }

    public function command($command, $help, $callback, $shortcut = null) {
        $callback = $callback->bindTo($this, $this);
        $this->commands[$command] = [
            'help' => $help,
            'callback' => $callback,
            'shortcut' => $shortcut
        ];
    }

    public function ask($question, $default = false, $hidden = false) {
        $this->print($question . ($default !== false ? " [$default]" : ''));
    
        if ($hidden) {
            system('stty -echo');
        }
    
        $answer = trim(fgets(STDIN));
    
        if ($hidden) {
            system('stty echo');
            echo PHP_EOL;
        }
    
        if (empty($answer)) {
            if ($default !== false) {
                $answer = $default;
            } else {
                return $this->ask($question, $default, $hidden);
            }
        }
    
        return $answer;
    }
    
    public function askYesNo($question) {
        $answer = $this->ask($question . ' [y/n]');
        if ($answer == 'y' || $answer == 'yes') {
            return true;
        }
    
        if ($answer == 'n' || $answer == 'no') {
            return false;
        }
    
        return $this->askYesNo($question);
    }

    public function print($message, $color = null, $bgColor = null) {
        $colors = [
            'black' => '0;30',
            'dark_gray' => '1;30',
            'blue' => '0;34',
            'light_blue' => '1;34',
            'green' => '0;32',
            'light_green' => '1;32',
            'cyan' => '0;36',
            'light_cyan' => '1;36',
            'red' => '0;31',
            'light_red' => '1;31',
            'purple' => '0;35',
            'light_purple' => '1;35',
            'brown' => '0;33',
            'yellow' => '1;33',
            'light_gray' => '0;37',
            'white' => '1;37'
        ];
    
        $bgColors = [
            'black' => '40',
            'white' => '47',
            'red' => '41',
            'green' => '42',
            'yellow' => '43',
            'blue' => '44',
            'magenta' => '45',
            'cyan' => '46',
            'light_gray' => '47',
        ];
    
        $colorCode = '';
        if ($color && isset($colors[$color])) {
            $colorCode = $colors[$color];
        }
    
        $bgColorCode = '';
        if ($bgColor && isset($bgColors[$bgColor])) {
            $bgColorCode = ';' . $bgColors[$bgColor];
        }
    
        $output = "\033[" . $colorCode . $bgColorCode . "m" . $message . "\033[0m" . PHP_EOL;
    
        echo $output;
    }

    public function printBox($message, $color = null, $bgColor = null) {
        $paddingSize = 2; // The amount of space you want on either side of the message
        $message = str_repeat(' ', $paddingSize) . $message . str_repeat(' ', $paddingSize);
        $boxWidth = strlen($message);
        $padding = str_repeat(' ', $boxWidth);
    
        $this->print($padding, $color, $bgColor);
        $this->print($message, $color, $bgColor);
        $this->print($padding, $color, $bgColor);
    }

    public function printError($message) {
        $this->print($message, 'white', 'red');
    }

    public function run() {
        if ($this->argc < 2 || $this->argv[1] == 'help' || $this->argv[1] == '-h') {
            $this->help();
            return;
        }
    
        if(isset($this->argv[2]) && $this->argv[2] == 'help' || isset($this->argv[2]) && $this->argv[2] == '-h') {
            $this->help($this->argv[1]);
            return;
        }
    
        $input = $this->argv[1];
        $command = null;
        foreach ($this->commands as $cmd => $data) {
            if ($cmd == $input || (isset($data['shortcut']) && '-' . $data['shortcut'] == $input)) {
                $command = $cmd;
                break;
            }
        }
    
        if (!$command) {
            echo "Command not found" . PHP_EOL;
            return;
        }
    
        $this->commands[$command]['callback']();
    }
}