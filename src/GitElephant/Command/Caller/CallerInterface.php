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
 * interface for the git command caller
 */
interface CallerInterface
{
    /**
     * execute a command
     *
     * @param string      $cmd the command
     * @param bool        $git prepend git to the command
     * @param null|string $cwd directory where the command should be executed
     *
     * @return CallerInterface
     */
    public function execute(
        string $cmd,
        bool $git = true,
        string $cwd = null
    ): CallerInterface;

    /**
     * after calling execute this method should return the output
     *
     * @param bool $stripBlankLines strips the blank lines
     *
     * @return array<string>
     */
    public function getOutputLines(bool $stripBlankLines = false): array;

    /**
     * Returns the output of the last executed command.
     * May be adjusted, such as trimmed.
     *
     * @return string
     */
    public function getOutput(): string;

    /**
     * Returns the output of the last executed command.
     * May not be adjusted, not trimmed or anything, really raw.
     *
     * @return string
     */
    public function getRawOutput(): string;

    /**
     * Get the binary path
     *
     * @return string
     */
    public function getBinaryPath(): string;

    /**
     * Get the binary version
     *
     * @return string
     */
    public function getBinaryVersion(): string;
}
