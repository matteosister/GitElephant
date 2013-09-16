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

namespace GitElephant\Command;

use GitElephant\Exception\NoSSH2ExtensionException;

/**
 * Caller via ssh2 PECL extension
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class CallerSSH2 implements CallerInterface
{
    /**
     * @var resource
     */
    private $resource;

    /**
     * @var string
     */
    private $gitPath;

    /**
     * the output lines of the command
     *
     * @var array
     */
    private $outputLines = array();

    /**
     * @param string $host    remote host
     * @param int    $port    remote port
     * @param string $gitPath path of the git executable on the remote host
     *
     * @throws \GitElephant\Exception\NoSSH2ExtensionException
     */
    public function __construct($host, $port = 22, $gitPath = '/usr/bin/git')
    {
        if (!function_exists('ssh2_connect')) {
            throw new NoSSH2ExtensionException;
        }
        $this->resource = ssh2_connect($host, $port);
        $this->gitPath = $gitPath;
    }

    /**
     * @param string $user     user
     * @param string $password password
     */
    public function setUserPasswordAuthentication($user, $password)
    {
        ssh2_auth_password($this->resource, $user, $password);
    }

    /**
     * execute a command
     *
     * @param string      $cmd the command
     * @param bool        $git prepend git to the command
     * @param null|string $cwd directory where the command should be executed
     *
     * @return CallerInterface
     */
    public function execute($cmd, $git = true, $cwd = null)
    {
        if ($git) {
            $cmd = $this->gitPath . ' ' . $cmd;
        }
        $stream = ssh2_exec($this->resource, $cmd);
        // rtrim values
        $values = array_map('rtrim', explode(PHP_EOL, stream_get_contents($stream)));
        $this->outputLines = $values;

        return $this;
    }

    /**
     * after calling execute this method should return the output
     *
     * @param bool $stripBlankLines strips the blank lines
     *
     * @return array
     */
    public function getOutputLines($stripBlankLines = false)
    {
        if ($stripBlankLines) {
            $output = array();
            foreach ($this->outputLines as $line) {
                if ('' !== $line) {
                    $output[] = $line;
                }
            }

            return $output;
        }

        return $this->outputLines;
    }
}