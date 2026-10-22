<?php
namespace BettIngenieure\PhpBuildConfig;

class PHP81Builder extends PHP81BaseBuilder {

    protected function getSubDirName() {
        return 'php-81';
    }

    protected function getFpmName() {
        return $this->getVersion();
    }

    protected function getVersion() {
        return 'php81';
    }
}