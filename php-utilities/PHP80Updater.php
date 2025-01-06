<?php
namespace BettIngenieure\PhpBuildConfig;

class PHP80Updater extends PHP8Updater {

    protected function getSubDirName() {
        return 'php-8';
    }

    protected function getFpmName() {
        return 'php8';
    }

    protected function getVersion() {
        return 'php-8.0';
    }

    protected function getPeclSwoolePackageName() {
        return 'swoole-5.1.6';
    }
}