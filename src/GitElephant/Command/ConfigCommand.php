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
use GitElephant\Command\Config\Config;

/**
 * ConfigCommand generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class ConfigCommand extends BaseCommand
{
    const GIT_CONFIG_COMMAND = 'config';

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
     *
     * @var string|Config $config
     * @return string
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
     *
     * @var string|Config $config
     * @return string
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
     * Like getAll, but interprets the name as a regular expression and writes out the key names.
     * Regular expression matching is currently case-sensitive and done against a canonicalized version of the key
     * in which section and variable names are lowercased, but subsection names are not.
     *
     * @var string|Config $config
     * @return string
     */
    public function getRegexp($config)
    {
        $this->clearAll();
        $this->addCommandName(self::GIT_CONFIG_COMMAND);
        $this->addFileOption();
        $this->addCommandArgument('--get-regexp');
        $this->addCommandSubject($config);
        return $this->getCommand();
    }

    /**
     * Sets a new config value
     *
     * @var string|Config $config
     * @var string $value
     * @return string
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
     *
     * @var string|Config $config
     * @var string $value
     * @return string
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

    /**
     * Remove the line matching the key from config file. (--unset)
     *
     * @var string|Config $config
     * @return string
     */
    public function uset($config)
    {
        $this->clearAll();
        $this->addCommandName(self::GIT_CONFIG_COMMAND);
        $this->addFileOption();
        $this->addCommandArgument('--unset');
        $this->addCommandSubject($config);
        return $this->getCommand();
    }

    /**
     * Remove all lines matching the key from config file. (--unset-all)
     *
     * @var string|Config $config
     * @return string
     */
    public function usetAll($config)
    {
        $this->clearAll();
        $this->addCommandName(self::GIT_CONFIG_COMMAND);
        $this->addFileOption();
        $this->addCommandArgument('--unset-all');
        $this->addCommandSubject($config);
        return $this->getCommand();
    }
}
