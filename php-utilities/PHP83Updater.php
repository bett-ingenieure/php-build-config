<?php
namespace BettIngenieure\PhpBuildConfig;

class PHP83Updater extends PHP8Updater {

    protected function getSubDirName() {
        return 'php-83';
    }

    protected function getFpmName() {
        return $this->getVersion();
    }

    protected function getVersion() {
        return 'php83';
    }
}