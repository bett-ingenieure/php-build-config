<?php
namespace BettIngenieure\PhpBuildConfig;

class PHP84Builder extends PHP80BaseBuilder {

    protected function getVersion() {
        return 'php84';
    }

    protected function getSubDirName() {
        return $this->getVersion();
    }

    protected function getFpmName() {
        return $this->getVersion();
    }
}