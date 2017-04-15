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

/**
 * ConfigCommand generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class ConfigCommand extends BaseCommand
{
    const GIT_CONFIG_COMMAND = 'config';

    /**
     * global context
     */
    const FILE_LOCATION_GLOBAL = '--global';

    /**
     * system context
     */
    const FILE_LOCATION_SYSTEM = '--system';

    /**
     * local context
     */
    const FILE_LOCATION_LOCAL = '--local';

    /**
     * @var string
     */
    private $context;

    /**
     * @param string $context execution context, global, system or local
     *                        use the provided constants
     *
     * @return ConfigCommand
     */
    public static function getInstance($context = self::FILE_LOCATION_LOCAL)
    {
        return new self($context);
    }

    /**
     * @param $context
     */
    private function __construct($context)
    {
        $this->context = $context;
    }

    private function addFileOption()
    {
        $this->addCommandArgument($this->context);
    }

    /**
     * Get the value for a given key (optionally filtered by a regex matching the value).
     * Returns error code 1 if the key was not found and error code 2 if multiple key values were found.
     */
    public function get($config)
    {
        $this->clearAll();
        $this->addCommandName(self::GIT_CONFIG_COMMAND);
        $this->addFileOption();
        $this->addCommandArgument('--get');
        $this->addCommandSubject($config);
        return $this->getCommand();
    }

    /**
     * Like get, but does not fail if the number of values for the key is not exactly one.
     */
    public function getAll($config)
    {
        $this->clearAll();
        $this->addCommandName(self::GIT_CONFIG_COMMAND);
        $this->addFileOption();
        $this->addCommandArgument('--get-all');
        $this->addCommandSubject($config);
        return $this->getCommand();
    }

    /**
     * Sets a new config value
     */
    public function set($config, $value)
    {
        $this->clearAll();
        $this->addCommandName(self::GIT_CONFIG_COMMAND);
        $this->addFileOption();
        $this->addCommandSubject($config);
        $this->addCommandSubject2($value);
        return $this->getCommand();
    }

    /**
     * Adds a new line to the option without altering any existing values.
     */
    public function add($config, $value)
    {
        $this->clearAll();
        $this->addCommandName(self::GIT_CONFIG_COMMAND);
        $this->addFileOption();
        $this->addCommandArgument('--add');
        $this->addCommandSubject($config);
        $this->addCommandSubject2($value);
        return $this->getCommand();
    }
}
