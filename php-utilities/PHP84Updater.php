<?php
namespace BettIngenieure\PhpBuildConfig;

class PHP84Updater extends PHP8Updater {

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