<?php

/**
 * GitElephant - An abstraction layer for git written in PHP
 * Copyright (C) 2013  Matteo Giachino
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see [http://www.gnu.org/licenses/].
 */

namespace GitElephant\Command\Caller;

/**
 * Caller via ssh2 PECL extension
 *
 * @author Matteo Giachino <matteog@gmail.com>
 * @author Tim Bernhard <tim@bernhard-webstudio.ch>
 */
class CallerSSH2 extends AbstractCaller
{
    /**
     * @param resource $resource
     * @param string   $gitPath path of the git executable on the remote host
     *
     * @internal param string $host remote host
     * @internal param int $port remote port
     */
    public function __construct(private $resource, $gitPath = '/usr/bin/git')
    {
        $this->binaryPath = $gitPath;
    }

    /**
     * execute a command
     *
     * @param string      $cmd the command
     * @param bool        $git prepend git to the command
     * @param null|string $cwd directory where the command should be executed
     */
    public function execute(
        $cmd,
        $git = true,
        $cwd = null
    ): \GitElephant\Command\Caller\CallerInterface {
        if ($git) {
            $gitCmd = $this->escapePosixArg($this->getBinaryPath());
            if ($cwd !== null && $cwd !== '') {
                $gitCmd .= ' -C ' . $this->escapePosixArg($cwd);
            }
            $cmd = 'LC_ALL=C ' . $gitCmd . ' ' . $cmd;
        } else {
            if ($cwd !== null && $cwd !== '') {
                $cmd = 'cd ' . $this->escapePosixArg($cwd) . ' && LC_ALL=C ' . $cmd;
            } else {
                $cmd = 'LC_ALL=C ' . $cmd;
            }
        }

        $stream = ssh2_exec($this->resource, $cmd);
        if ($stream === false) {
            throw new \RuntimeException('Failed to execute remote SSH2 command.');
        }

        stream_set_blocking($stream, true);
        $data = stream_get_contents($stream);
        fclose($stream);

        $this->rawOutput = $data === false ? '' : $data;
        // rtrim values
        $values = array_map(rtrim(...), explode(PHP_EOL, $this->rawOutput));
        $this->outputLines = $values;

        return $this;
    }

    /**
     * Escape a single argument using POSIX single-quoting, independent of local OS/locale.
     */
    private function escapePosixArg(string $arg): string
    {
        return "'" . str_replace("'", "'\\''", $arg) . "'";
    }
}
