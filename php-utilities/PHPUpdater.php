<?php
namespace BettIngenieure\PhpBuildConfig;

abstract class PHPUpdater {

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

    abstract protected function getFpmName();
    abstract protected function getSubDirName();

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

    abstract protected function updateDependencies();

    abstract protected function getVersion();

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

    abstract protected function install(string $version);
    abstract protected function installExtensions(string $version);

    protected function getFpmSourceName() {
        return $this->getFpmName();
    }

    abstract protected function getPHPININame();

    /**
     * @param string $version
     * @return void
     * @throws ExceptionExec
     */
    protected function activate(string $version) {

        chdir($this->subDir . $version);

        $initScriptSource = $this->repositoryDir . 'Assets/' . $this->getFpmSourceName() . '-fpm';
        $initScriptTarget = '/etc/init.d/' . $this->getFpmName() . '-fpm';

        $this->refreshLink(
            $initScriptSource,
            $initScriptTarget,
            function() use($initScriptTarget) { try { $this->system->exec($initScriptTarget . ' stop'); } catch(\BettIngenieure\PhpBuildConfig\ExceptionExec $e) {}
            });
        $this->system->exec('chmod +x ' . escapeshellarg($initScriptSource));

        $this->refreshLink(
            $this->repositoryDir . 'Assets/' . $this->getPHPININame() . '.ini',
            $this->subDir . $version . '/lib/php.ini'
        );

        $this->refreshLink(
            $this->subDir . $version,
            $this->subDir . 'current-live'
        );

        try { $this->system->exec($initScriptTarget . ' start'); } catch(\BettIngenieure\PhpBuildConfig\ExceptionExec $e) {}
    }

    protected function refreshLink(string $source, string $target, \Closure $beforeCallback = null) {

        if(
            file_exists($target)
            || is_link($target)
        ) {

            if($beforeCallback !== null) {
                $beforeCallback();
            }

            $this->system->exec('rm ' . escapeshellarg($target));
        }

        $this->system->exec('ln -s ' . escapeshellarg($source) . ' ' . escapeshellarg($target));
    }

    /**
     * @return void
     */
    protected function end() {
        $this->log->write(PHP_EOL . 'End: ' . date("d.m.Y H:i:s"));
    }
}