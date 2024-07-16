<?php
namespace BettIngenieure\PhpBuildConfig;

class PHP8Updater extends PHPUpdater {

    /**
     * Should be something like php-8, php-80, php-81.
     *
     * @return string
     */
    protected function getSubDirName() {
        return 'php-8';
    }

    protected function getFpmName() {
        return 'php8';
    }

    /**
     * Used to get download
     *
     * @return string
     */
    protected function getVersion() {
        return 'php-8.0';
    }

    protected function getPHPININame() {
        return 'php8';
    }

    /**
     * @return void
     * @throws ExceptionExec
     */
    protected function updateDependencies() {

        // Update Dependencies
        $this->system->exec('apt-get update');
        // Copied from PHP7 + PHP7.4
        $this->system->exec('apt-get install -y \
g++ pkg-config make libzip-dev autoconf libfcgi-dev libfcgi0ldbl libjpeg62-turbo-dev libmcrypt-dev libssl-dev libc-client2007e \
libc-client2007e-dev libxml2-dev libbz2-dev libcurl4-openssl-dev libjpeg-dev libpng-dev libfreetype6-dev libkrb5-dev libpq-dev \
libxslt1-dev libmagickwand-dev libsqlite3-dev libonig-dev libwebp-dev libxpm-dev libargon2-dev'
        );

        $this->system->exec('apt-get install -y git');

        $multiarch = $this->getGccMultiarch();

        if(!file_exists('/usr/lib/' . $multiarch . '/libc-client.a')) {
            $this->system->exec('ln -s /usr/lib/libc-client.a /usr/lib/' . $multiarch . '/libc-client.a');
        }
    }

    private function getGccMultiarch() : string {
        return $this->system->exec('gcc -print-multiarch')[0];
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
--with-libdir=/lib/' . $this->getGccMultiarch() . ' \
--enable-ftp \
--with-imap \
--with-imap-ssl \
--with-kerberos \
--with-gettext \
--with-libxml \
--with-password-argon2 \
--enable-fpm \
--enable-intl \
--enable-bcmath \
--enable-exif \
--enable-shmop \
--enable-sysvmsg');

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

        if($this->shouldBuildImagickExtensionFromSource()) {
            $this->buildImagickExtensionFromSource($version);
            chdir($this->subDir . $version . '/bin');
        } else {
            $this->system->exec("yes '' 2>&1 | ./pecl install imagick");
        }

        $this->system->exec("yes '' 2>&1 | ./pecl install apcu");
        $this->system->exec("yes '' 2>&1 | ./pecl install redis");
        $this->system->exec("yes '' 2>&1 | ./pecl install --configureoptions 'enable-openssl=\"yes\"' swoole");
    }

    protected function shouldBuildImagickExtensionFromSource() : bool {
        return false;
    }

    protected function buildImagickExtensionFromSource(string $version) {

        mkdir($this->subDir . $version . '/ext/imagick');
        $this->system->exec('wget -qO- https://github.com/Imagick/imagick/archive/28f27044e435a2b203e32675e942eb8de620ee58.tar.gz | tar xvz -C "' . $this->subDir . $version . '/ext/imagick' . '" --strip 1');

        chdir($this->subDir . $version . '/ext/imagick');

        $this->system->exec($this->subDir . $version . '/bin/phpize');
        $this->system->exec('./configure --with-php-config=' . $this->subDir . $version . '/bin/php-config');
        $this->system->exec('make -j 4');
        $this->system->exec('make install');
    }
}