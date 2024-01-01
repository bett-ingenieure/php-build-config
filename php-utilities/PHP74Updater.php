<?php
namespace BettIngenieure\PhpBuildConfig;

class PHP74Updater extends PHPUpdater {

    /**
     * Should be something like php-8, php-80, php-81.
     *
     * @return string
     */
    protected function getSubDirName() {
        return 'php-7.4';
    }

    protected function getFpmName() {
        return 'php7.4';
    }

    protected function getFpmSourceName() {
        return 'php7_4';
    }

    protected function getPHPININame() {
        return 'php7';
    }

    /**
     * Used to get download
     *
     * @return string
     */
    protected function getVersion() {
        return 'php-7.4';
    }

    /**
     * @return void
     * @throws ExceptionExec
     */
    protected function updateDependencies() {

        // Update Dependencies
        $this->system->exec('apt-get update');
        // Copied from PHP7 + PHP7.4
        $this->system->exec('apt-get install -y make autoconf pkg-config libsqlite3-dev libonig-dev libwebp-dev libxpm-dev libxml2-dev libc-client-dev libkrb5-dev libssl-dev libzip-dev libbz2-dev libcurl4-openssl-dev libjpeg-dev libpng-dev libfreetype6-dev libmagickwand-dev');

        $this->system->exec('apt-get install -y git');
        if(!file_exists('/usr/lib/x86_64-linux-gnu/libc-client.a')) {
            $this->system->exec('ln -s /usr/lib/libc-client.a /usr/lib/x86_64-linux-gnu/libc-client.a');
        }
    }

    /**
     * @param string $version
     * @return void
     * @throws ExceptionExec
     */
    protected function install(string $version) {


        chdir($this->subDir . $version);

        $this->system->exec('./configure \
--prefix=' . escapeshellarg($this->subDir . $version . '/') . ' \
--enable-mbstring \
--enable-soap \
--enable-calendar \
--with-curl \
--enable-gd \
--with-freetype \
--disable-rpath \
--enable-inline-optimization \
--with-bz2 \
--with-zlib \
--with-zip \
--with-pear \
--enable-sockets \
--enable-sysvsem \
--enable-sysvshm \
--enable-pcntl \
--enable-mbregex \
--with-mhash \
--with-pdo-mysql \
--with-mysqli \
--with-jpeg=/usr/include/ \
--with-xpm=/usr/include/ \
--with-webp=/usr/include/ \
--with-openssl \
--with-fpm-user=www-data \
--with-fpm-group=www-data \
--with-libdir=/lib/x86_64-linux-gnu \
--enable-ftp \
--with-imap \
--with-imap-ssl \
--with-kerberos \
--with-gettext \
--with-libxml \
--enable-fpm \
--enable-intl \
--enable-bcmath \
--enable-exif \
--enable-shmop \
--enable-sysvmsg \
--with-xmlrpc \
--with-pear');

        $this->system->exec('make -j 4');
        $this->system->exec('make install');
    }

    /**
     * @param string $version
     * @return void
     * @throws ExceptionExec
     */
    protected function installExtensions(string $version) {

        chdir($this->subDir . $version . '/bin');
        $this->system->exec('./pecl -C ./pear.conf update-channels');
        $this->system->exec("yes '' 2>&1 | ./pecl install imagick");
        $this->system->exec("yes '' 2>&1 | ./pecl install apcu");
        $this->system->exec("yes '' 2>&1 | ./pecl install apcu_bc-beta");
        $this->system->exec("yes '' 2>&1 | ./pecl install redis");
    }
}