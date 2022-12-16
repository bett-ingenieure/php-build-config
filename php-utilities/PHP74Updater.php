<?php
namespace BettIngenieure\PhpBuildConfig;

class PHP74Updater {

    /**
     * @var Log
     */
    protected $log;

    /**
     * @var System
     */
    protected $system;

    protected $subDir;
    protected $repositoryDir;

    public function __construct(Log $log) {

        $this->log = $log;

        $this->system = new System();
        $this->system->setLogger($log);
        // $this->system->setVerbose(true);

        $rootDir = dirname(__FILE__, 3) . DIRECTORY_SEPARATOR;
        $this->subDir = $rootDir . $this->getSubDirName() . DIRECTORY_SEPARATOR;

        $this->repositoryDir = dirname(__FILE__, 2) . DIRECTORY_SEPARATOR;
    }

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
     */
    protected function start() {

        putenv('DEBIAN_FRONTEND=noninteractive');

        $this->log->write('Start: ' . date("d.m.Y H:i:s") . PHP_EOL);
    }

    /**
     * @return void
     * @throws ExceptionExec
     */
    public function execute() {

        $this->start();

        $this->updateDependencies();

        if(!file_exists($this->subDir)) {
            mkdir($this->subDir, 0755, true);
        }

        // Download
        if(($version = $this->download()) === null) {
            // Target does already exist - there is no update
            $this->log->write('Target does already exist - exiting');
            return;
        }

        $this->install($version);
        $this->installExtensions($version);
        $this->activate($version);

        $this->end();
    }

    /**
     * @return void
     * @throws ExceptionExec
     */
    protected function updateDependencies() {

        // Update Dependencies
        $this->system->exec('apt-get update');
        // Copied from PHP7 + PHP7.4
        $this->system->exec('apt-get install -y pkg-config libsqlite3-dev libonig-dev libwebp-dev libxpm-dev');
    }

    /**
     * @return string|null
     * @throws ExceptionExec
     */
    protected function download() {

        chdir($this->subDir);

        if(file_exists($this->subDir . 'download')) {
            unlink($this->subDir . 'download');
        }
        $this->system->exec('wget -O download https://config.bett-ingenieure.de/getLatestVersion?target=' . $this->getVersion() . '.tar.gz');

        $output = $this->system->exec('tar -tzf download 2>&1 | head -1 | cut -f1 -d"/"');
        $target = $output[0];
        if(preg_match('/php-\d+\.\d+\.\d+/', $target) !== 1) {
            throw new \RuntimeException('Invalid target folder match: ' . var_export($output, true));
        }

        if(file_exists($this->subDir . $target)) {
            return null; // Target does already exist - there is no update
            //$this->system->exec('rm -R ' . $subDir . $target);
        }

        $this->system->exec('tar xfvz download');
        $this->system->exec('chown -R root:root ' . $this->subDir . $target);
        unlink($this->subDir . 'download');

        return $target;
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

    /**
     * @param string $version
     * @return void
     * @throws ExceptionExec
     */
    protected function activate(string $version) {

        chdir($this->subDir . $version);

        $initScript = '/etc/init.d/' . $this->getFpmName() . '-fpm';

        if(file_exists($initScript) || is_link($initScript)) {
            try { $this->system->exec($initScript . ' stop'); } catch(\BettIngenieure\PhpBuildConfig\ExceptionExec $e) {}
            $this->system->exec('rm ' . escapeshellarg($initScript));
        }

        $this->system->exec('ln -s ' . escapeshellarg($this->repositoryDir . 'php7_4-fpm') . ' ' . escapeshellarg($initScript));
        $this->system->exec('chmod +x ' . escapeshellarg($this->repositoryDir . 'php7_4-fpm'));

        chdir($this->subDir . $version . '/lib');
        $this->system->exec('ln -s ' . escapeshellarg($this->repositoryDir . 'php7.ini') . ' php.ini');

        chdir($this->subDir);
        if(file_exists($this->subDir . 'current-live') || is_link(file_exists($this->subDir . 'current-live'))) {
            $this->system->exec('rm ' . escapeshellarg($this->subDir . 'current-live'));
        }
        $this->system->exec('ln -s ' . escapeshellarg($this->subDir . $version) . ' current-live');

        try { $this->system->exec($initScript . ' start'); } catch(\BettIngenieure\PhpBuildConfig\ExceptionExec $e) {}
    }

    /**
     * @return void
     */
    protected function end() {
        $this->log->write(PHP_EOL . 'End: ' . date("d.m.Y H:i:s"));
    }
}