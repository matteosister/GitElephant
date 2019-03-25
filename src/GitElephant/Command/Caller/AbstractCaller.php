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
 * AbstractCaller
 *
 * @author Tim Bernhard <tim@bernhard-webstudio.ch>
 */
abstract class AbstractCaller implements CallerInterface
{
    /**
     * Git binary path
     *
     * @var string
     */
    protected $binaryPath;

    /**
     * Git binary version
     *
     * @var string
     */
    protected $binaryVersion;

    /**
     * the output lines of the command
     *
     * @var array
     */
    protected $outputLines = array();

    /**
     * @inheritdoc
     */
    public function getBinaryPath()
    {
        return $this->binaryPath;
    }

    /**
     * path setter
     *
     * @param string $path the path to the system git binary
     */
    public function setBinaryPath(string $path)
    {
        $this->binaryPath = $path;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBinaryVersion()
    {
        if (is_null($this->binaryVersion)) {
            $version = $this->execute('--version')->getOutput();
            if (!preg_match('/^git version [0-9\.]+/', $version)) {
                throw new \RuntimeException('Could not parse git version. Unexpected format "' . $version . '".');
            }
            $this->binaryVersion = preg_replace('/^git version ([0-9\.]+)/', '$1', $version);
        }

        return $this->binaryVersion;
    }

    /**
     * returns the output of the last executed command
     *
     * @return string
     */
    public function getOutput()
    {
        return implode("\n", $this->outputLines);
    }

    /**
     * returns the output of the last executed command as an array of lines
     *
     * @param bool $stripBlankLines remove the blank lines
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
