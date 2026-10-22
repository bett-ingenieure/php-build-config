<?php

namespace BettIngenieure\PhpBuildConfig;

abstract class PHP81BaseBuilder extends PHP80BaseBuilder {

    protected function updateDependencies() {
        parent::updateDependencies();

        $this->system->exec('apt-get install -y curl');
    }

    /**
     * @param string $version
     * @return void
     * @throws ExceptionExec
     */
    protected function installExtensions(string $version) {

        $binPath = $this->subDir . $version . '/bin';

        $this->system->exec('curl -fL --output ./pie.phar https://github.com/php/pie/releases/latest/download/pie.phar');

        if($this->shouldBuildImagickExtensionFromSource()) {
            $this->buildImagickExtensionFromSource($version);
            chdir($binPath);
        } else {
            $this->system->exec("yes '' 2>&1 | ./php pie.phar install imagick/imagick --with-phpize-path " . $binPath . "/phpize --with-php-config " . $binPath . "/php-config");
        }

        $this->system->exec("yes '' 2>&1 | ./php pie.phar install apcu/apcu --with-phpize-path " . $binPath . "/phpize --with-php-config " . $binPath . "/php-config");
        $this->system->exec("yes '' 2>&1 | ./php pie.phar install phpredis/phpredis --with-phpize-path " . $binPath . "/phpize --with-php-config " . $binPath . "/php-config");
        $this->system->exec("yes '' 2>&1 | ./php pie.phar install swoole/swoole --with-phpize-path " . $binPath . "/phpize --with-php-config " . $binPath . "/php-config");
    }
}