<?php

require __DIR__ . '/php-utilities/_autoload.php';

$logger = new \BettIngenieure\PhpBuildConfig\Log(__FILE__ . '.log');

//
//  WARNING: Bookworm's Ghostscript (V10.0.0) had a bug (infinity loop in 08/26) -> use Trixie
//

$updater = new \BettIngenieure\PhpBuildConfig\PHP83Builder($logger);
$updater->execute();