<?php
namespace BettIngenieure\PhpBuildConfig;

class PHP86Builder extends PHP80BaseBuilder {

    protected function getVersion() {
        return 'php86';
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