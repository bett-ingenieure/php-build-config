<?php
namespace BettIngenieure\PhpBuildConfig;

if(
    !defined('PHP_MAJOR_VERSION')
    || PHP_MAJOR_VERSION < 7
) {
    echo 'At least PHP version 7 is required.';
    exit(1);
}

spl_autoload_register(function($class) {

    $classInNamespace = substr($class, strlen(__NAMESPACE__)+1);

    require __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $classInNamespace) . '.php';
});