<?php

require __DIR__ . '/php-utilities/_autoload.php';

$logger = new \BettIngenieure\PhpBuildConfig\Log(__FILE__ . '.log');

$updater = new \BettIngenieure\PhpBuildConfig\PHP83Updater($logger);
$updater->execute();