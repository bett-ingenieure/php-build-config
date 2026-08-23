<?php
namespace BettIngenieure\PhpBuildConfig;

class PHP86Updater extends PHP8Updater {

    protected function getVersion() {
        return 'php86';
    }

    protected function getSubDirName() {
        return $this->getVersion();
    }

    protected function getFpmName() {
        return $this->getVersion();
    }
}