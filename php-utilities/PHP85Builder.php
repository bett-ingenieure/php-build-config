<?php
namespace BettIngenieure\PhpBuildConfig;

class PHP85Builder extends PHP81BaseBuilder {

    protected function getVersion() {
        return 'php85';
    }

    protected function getSubDirName() {
        return $this->getVersion();
    }

    protected function getFpmName() {
        return $this->getVersion();
    }

    protected function getPHPININame() {
        return 'php85';
    }
}