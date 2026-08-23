<?php
namespace BettIngenieure\PhpBuildConfig;

class PHP80Builder extends PHP80BaseBuilder {

    protected function getSubDirName() {
        return 'php-8';
    }

    protected function getFpmName() {
        return 'php8';
    }

    protected function getVersion() {
        return 'php80';
    }

    protected function getPeclSwoolePackageName() {
        return 'swoole-5.1.6';
    }
}