<?php

require __DIR__ . '/php-utilities/_autoload.php';

$logger = new \BettIngenieure\PhpBuildConfig\Log(__FILE__ . '.log');

//
//  WARNING: Does not build on Debian Bookworm (OpenSSL3...)
//

$updater = new \BettIngenieure\PhpBuildConfig\PHP80Updater($logger);
$updater->execute();