<?php
namespace BettIngenieure\PhpBuildConfig;

class PHP84Updater extends PHP8Updater {

    protected function getSubDirName() {
        return 'php-84';
    }

    protected function getFpmName() {
        return 'php84';
    }

    protected function getVersion() {
        return 'php-8.4';
    }

    protected function shouldBuildImagickExtensionFromSource() : bool {
        return true;
    }
}