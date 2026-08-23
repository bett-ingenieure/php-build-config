<?php
namespace BettIngenieure\PhpBuildConfig;

class PHP81Updater extends PHP8Updater {

    protected function getSubDirName() {
        return 'php-81';
    }

    protected function getFpmName() {
        return $this->getVersion();
    }

    protected function getVersion() {
        return 'php81';
    }

    protected function getPeclSwoolePackageName() {
        return 'swoole-6.1.7';
    }
}