<?php

require __DIR__ . '/php-utilities/_autoload.php';

$logger = new \BettIngenieure\PhpBuildConfig\Log(__FILE__ . '.log');

//
//  WARNING: untested on Bookworm -> use Trixie
//

$updater = new \BettIngenieure\PhpBuildConfig\PHP86Builder($logger);
$updater->execute();