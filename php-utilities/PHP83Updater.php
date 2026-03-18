<?php
namespace BettIngenieure\PhpBuildConfig;

class PHP83Updater extends PHP8Updater {

    protected function getSubDirName() {
        return 'php-83';
    }

    protected function getFpmName() {
        return 'php83';
    }

    protected function getVersion() {
        return 'php-8.3';
    }
}