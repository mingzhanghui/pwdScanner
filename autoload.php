<?php

$prefix = "";

spl_autoload_register(function($class) use($prefix) {
    $base_dir = dirname(__FILE__).'/'.$prefix.'/'. str_replace('\\', '/', $prefix);
    // echo $base_dir.PHP_EOL;
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    // echo $file.PHP_EOL;
    if (file_exists($file)) {
        require $file;
    }
});
