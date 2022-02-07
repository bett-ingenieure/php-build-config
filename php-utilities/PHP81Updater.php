<?php
namespace BettIngenieure\PhpBuildConfig;

class PHP81Updater extends PHP8Updater {

    protected function getSubDirName() {
        return 'php-81';
    }

    protected function getFpmName() {
        return 'php81';
    }

    protected function getVersion() {
        return 'php-8.1';
    }
}