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

namespace GitElephant\Command\Remote;

use GitElephant\Command\SubCommandCommand;
use GitElephant\Repository;

/**
 * Class AddRemoteCommand
 *
 * remote subcommand generator for add
 *
 * @package GitElephant\Objects
 * @author  David Neimeyer <davidneimeyer@gmail.com>
 */

class AddSubCommand extends SubCommandCommand
{
    public const GIT_REMOTE_ADD = 'add';
    public const GIT_REMOTE_ADD_OPTION_FETCH = '-f';
    public const GIT_REMOTE_ADD_OPTION_TAGS = '--tags';
    public const GIT_REMOTE_ADD_OPTION_NOTAGS = '--no-tags';
    public const GIT_REMOTE_ADD_OPTION_MIRROR = '--mirror';
    public const GIT_REMOTE_ADD_OPTION_SETHEAD = '-m';
    public const GIT_REMOTE_ADD_OPTION_TRACK = '-t';

    /**
     * constructor
     *
     * @param \GitElephant\Repository $repo The repository object this command
     *                                      will interact with
     */
    public function __construct(Repository $repo = null)
    {
        parent::__construct($repo);
    }

    /**
     * Valid options for remote command that require an associated value
     *
     * @return array Array of all value-required options
     */
    public function addCmdValueOptions(): array
    {
        return [
            self::GIT_REMOTE_ADD_OPTION_TRACK => self::GIT_REMOTE_ADD_OPTION_TRACK,
            self::GIT_REMOTE_ADD_OPTION_MIRROR => self::GIT_REMOTE_ADD_OPTION_MIRROR,
            self::GIT_REMOTE_ADD_OPTION_SETHEAD => self::GIT_REMOTE_ADD_OPTION_SETHEAD,
        ];
    }

    /**
     * switch only options for the add subcommand
     *
     * @return array
     */
    public function addCmdSwitchOptions(): array
    {
        return [
            self::GIT_REMOTE_ADD_OPTION_TAGS => self::GIT_REMOTE_ADD_OPTION_TAGS,
            self::GIT_REMOTE_ADD_OPTION_NOTAGS => self::GIT_REMOTE_ADD_OPTION_NOTAGS,
            self::GIT_REMOTE_ADD_OPTION_FETCH => self::GIT_REMOTE_ADD_OPTION_FETCH,
        ];
    }

    /**
     * build add sub command
     *
     * @param string $name    remote name
     * @param string $url     URL of remote
     * @param array  $options options for the add subcommand
     *
     * @return AddSubCommand
     */
    public function prepare($name, $url, $options = []): self
    {
        $options = $this->normalizeOptions(
            $options,
            $this->addCmdSwitchOptions(),
            $this->addCmdValueOptions()
        );
        
        $this->addCommandName(self::GIT_REMOTE_ADD);
        $this->addCommandSubject($name);
        $this->addCommandSubject($url);
        foreach ($options as $option) {
            $this->addCommandArgument($option);
        }

        return $this;
    }
}
