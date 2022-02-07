<?php

/**
 *
 * Copyright (C) 2022, Bett Ingenieure GmbH - All Rights Reserved
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL Bett Ingenieure GmbH BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 */

namespace BettIngenieure\PhpBuildConfig;

class System {

    /** @var bool $verbose */
    private $verbose = false;
    public function setVerbose(bool $verbose = true) {
        $this->verbose = $verbose;
    }

    /** @var Log|null $log */
    private $log;
    public function setLogger(Log $log) {
        $this->log = $log;
    }

    /**
     * @param string $cmd
     * @param int|null $expectedReturnVar
     * @return array
     * @throws ExceptionExec
     */
    public function exec(string $cmd, int $expectedReturnVar = null) : array {

        if($expectedReturnVar === null) {
            $expectedReturnVar = 0;
        }

        $cmd = 'timeout 1h ' . $cmd . ' 2>&1';

        if($this->verbose) {
            echo 'CMD: ' . $cmd . PHP_EOL;
        }

        if($this->log) {
            $this->log->write($cmd);
        }

        exec($cmd, $output, $return_var);
        if($this->log) {
            $this->log->writeAll(array_map(function($string) { return '-> ' . $string; }, $output));
        }
        if($this->verbose) {
            foreach($output as $line) {
                echo '-> ' . $line . PHP_EOL;
            }
        }

        if($return_var !== $expectedReturnVar) {

            $message = 'Unexpected return var ' .
                var_export($return_var, true) .
                ' while executing: ' . $cmd;

            if($this->log) {
                $this->log->write($message . ' -> stopped');
            }

            throw new ExceptionExec($message, $output);
        }

        return $output;
    }
}