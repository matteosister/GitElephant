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

use PhpCollection\Map;
use PhpCollection\Sequence;

/**
 * CommandFactory
 *
 * Factory class to instantiate the commands classes with global args
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class CommandFactory
{
    /**
     * @var string
     */
    private $baseNamespace;

    /**
     * @var Sequence
     */
    private $globalArguments;

    /**
     * @var Map
     */
    private $globalOptions;

    /**
     * @var Map
     */
    private $globalConfigs;

    /**
     * class constructor
     * @param array $arguments
     * @param array $options
     * @param array $configs
     */
    private function __construct(array $arguments = array(), array $options = array(), array $configs = array())
    {
        $this->baseNamespace = 'GitElephant\Command';
        $this->globalArguments = new Sequence($arguments);
        $this->globalOptions = new Map($options);
        $this->globalConfigs = new Map($configs);
    }

    /**
     * @param array $arguments
     * @param array $options
     * @param array $configs
     * @return CommandFactory
     */
    public static function create($arguments = array(), array $options = array(), $configs = array())
    {
        return new self($arguments, $options, $configs);
    }

    /**
     * @param string $arg
     */
    public function addArgument($arg)
    {
        $this->globalArguments->add($arg);
    }

    /**
     * @param $key
     * @param $value
     */
    public function addOption($key, $value)
    {
        $this->globalOptions->set($key, $value);
    }

    /**
     * @param $key
     * @param $value
     */
    public function addConfig($key, $value)
    {
        $this->globalConfigs->set($key, $value);
    }

    /**
     * instantiate a Command class
     *
     * @param $name
     *
     * @return BaseCommand
     */
    public function get($name)
    {
        $className = $this->camelize($name);
        /** @var BaseCommand $command */
        $command = new $className();
        if ($this->globalArguments->count() > 0) {
            foreach ($this->globalArguments as $argument) {
                $command->addGlobalCommandArgument($argument);
            }
        }
        if ($this->globalOptions->count() > 0) {
            $command->addGlobalOptions($this->globalOptions);
        }
        if ($this->globalConfigs->count() > 0) {
            $command->addGlobalConfigs($this->globalConfigs);
        }
        return $command;
    }

    /**
     * turns a command name in a FQDN of the command class
     *
     * @param $name
     * @return string
     */
    private function camelize($name)
    {
        $camelizedName = preg_replace_callback('/(^|_)([a-z])/', function ($matches) {
            return strtoupper($matches[2]);
        }, $name);
        $parts = explode('.', $camelizedName);
        $parts = array_map('ucfirst', $parts);
        return $this->baseNamespace.'\\'.implode('\\', $parts).'Command';
    }
}
