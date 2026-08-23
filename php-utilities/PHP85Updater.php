<?php
namespace BettIngenieure\PhpBuildConfig;

class PHP85Updater extends PHP8Updater {

    protected function getVersion() {
        return 'php85';
    }

    protected function getSubDirName() {
        return $this->getVersion();
    }

    protected function getFpmName() {
        return $this->getVersion();
    }
}