<?php
namespace BettIngenieure\PhpBuildConfig;

class PHP82Builder extends PHP81BaseBuilder {

    protected function getSubDirName() {
        return 'php-82';
    }

    protected function getFpmName() {
        return $this->getVersion();
    }

    protected function getVersion() {
        return 'php82';
    }
}