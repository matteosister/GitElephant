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

use GitElephant\Repository;
use PhpCollection\Map;

/**
 * BaseCommand
 *
 * The base class for all the command generators
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class BaseCommand
{
    /**
     * the command name
     *
     * @var string|null
     */
    private $commandName = null;

    /**
     * config options
     *
     * @var array
     */
    private $configs = [];

    /**
     * global configs
     *
     * @var array
     */
    private $globalConfigs = [];

    /**
     * global options
     *
     * @var array
     */
    private $globalOptions = [];

    /**
     * the command arguments
     *
     * @var array
     */
    private $commandArguments = [];

    /**
     * the global command arguments
     *
     * @var array
     */
    private $globalCommandArguments = [];

    /**
     * the command subject
     *
     * @var string|SubCommandCommand|null
     */
    private $commandSubject = null;

    /**
     * the command second subject (i.e. for branch)
     *
     * @var string|SubCommandCommand|null
     */
    private $commandSubject2 = null;

    /**
     * the path
     *
     * @var string|null
     */
    private $path = null;

    /**
     * @var string|null
     */
    private $binaryVersion;

    /**
     * @var Repository|null
     */
    private $repo;

    /**
     * constructor
     *
     * should be called by all child classes' constructors to permit use of
     * global configs, options and command arguments
     *
     * @param null|\GitElephant\Repository $repo The repo object to read
     */
    public function __construct(Repository $repo = null)
    {
        if (!is_null($repo)) {
            $this->addGlobalConfigs($repo->getGlobalConfigs());
            $this->addGlobalOptions($repo->getGlobalOptions());

            $arguments = $repo->getGlobalCommandArguments();
            if (!empty($arguments)) {
                foreach ($arguments as $argument) {
                    $this->addGlobalCommandArgument($argument);
                }
            }
            $this->repo = $repo;
        }
    }

    /**
     * Clear all previous variables
     */
    public function clearAll(): void
    {
        $this->commandName = null;
        $this->configs = [];
        $this->commandArguments = [];
        $this->commandSubject = null;
        $this->commandSubject2 = null;
        $this->path = null;
        $this->binaryVersion = null;
    }

    /**
     * Get a new instance of this command
     *
     * @param Repository $repo
     * @return static
     */
    public static function getInstance(Repository $repo = null)
    {
        return new static($repo);
    }

    /**
     * Add the command name
     *
     * @param string $commandName the command name
     */
    protected function addCommandName(string $commandName): void
    {
        $this->commandName = $commandName;
    }

    /**
     * Get command name
     *
     * @return string
     */
    protected function getCommandName(): string
    {
        return $this->commandName;
    }

    /**
     * Set Configs
     *
     * @param array|Map $configs the config variable. i.e. { "color.status" => "false", "color.diff" => "true" }
     */
    public function addConfigs($configs): void
    {
        foreach ($configs as $config => $value) {
            $this->configs[$config] = $value;
        }
    }

    /**
     * Set global configs
     *
     * @param array|Map $configs the config variable. i.e. { "color.status" => "false", "color.diff" => "true" }
     */
    protected function addGlobalConfigs($configs): void
    {
        if (!empty($configs)) {
            foreach ($configs as $config => $value) {
                $this->globalConfigs[$config] = $value;
            }
        }
    }

    /**
     * Set global option
     *
     * @param array|Map $options a global option
     */
    protected function addGlobalOptions($options): void
    {
        if (!empty($options)) {
            foreach ($options as $name => $value) {
                $this->globalOptions[$name] = $value;
            }
        }
    }

    /**
     * Get Configs
     *
     * @return array
     */
    public function getConfigs(): array
    {
        return $this->configs;
    }

    /**
     * Add a command argument
     *
     * @param string $commandArgument the command argument
     */
    protected function addCommandArgument($commandArgument): void
    {
        $this->commandArguments[] = $commandArgument;
    }

    /**
     * Add a global command argument
     *
     * @param string $commandArgument the command argument
     */
    protected function addGlobalCommandArgument($commandArgument): void
    {
        if (!empty($commandArgument)) {
            $this->globalCommandArguments[] = $commandArgument;
        }
    }

    /**
     * Get all added command arguments
     *
     * @return array
     */
    protected function getCommandArguments(): array
    {
        return $this->commandArguments !== [] ? $this->commandArguments : [];
    }

    /**
     * Add a command subject
     *
     * @param SubCommandCommand|array|string $commandSubject the command subject
     */
    protected function addCommandSubject($commandSubject): void
    {
        $this->commandSubject = $commandSubject;
    }

    /**
     * Add a second command subject
     *
     * @param SubCommandCommand|array|string $commandSubject2 the second command subject
     */
    protected function addCommandSubject2($commandSubject2): void
    {
        $this->commandSubject2 = $commandSubject2;
    }

    /**
     * Add a path to the git command
     *
     * @param string $path path
     */
    protected function addPath($path): void
    {
        $this->path = $path;
    }

    /**
     * Normalize any valid option to its long name
     * an provide a structure that can be more intelligently
     * handled by other routines
     *
     * @param array $options       command options
     * @param array $switchOptions list of valid options that are switch like
     * @param array $valueOptions  list of valid options that must have a value assignment
     *
     * @return array Associative array of valid, normalized command options
     */
    public function normalizeOptions(
        array $options = [],
        array $switchOptions = [],
        $valueOptions = []
    ): array {
        $normalizedOptions = [];

        foreach ($options as $option) {
            if (array_key_exists($option, $switchOptions)) {
                $normalizedOptions[$switchOptions[$option]] = $switchOptions[$option];
            } else {
                $parts = preg_split('/([\s=])+/', $option, 2, PREG_SPLIT_DELIM_CAPTURE);
                if (!empty($parts) && is_array($parts)) {
                    $optionName = $parts[0];
                    if (in_array($optionName, $valueOptions)) {
                        $value = $parts[1] === '=' ? $option : [$parts[0], $parts[2]];
                        $normalizedOptions[$optionName] = $value;
                    }
                }
            }
        }

        return $normalizedOptions;
    }

    /**
     * Get the current command
     *
     * @return string
     * @throws \RuntimeException
     */
    public function getCommand(): string
    {
        if (is_null($this->commandName)) {
            throw new \RuntimeException("You should pass a commandName to execute a command");
        }

        $command = '';
        $command .= $this->getCLIConfigs();
        $command .= $this->getCLIGlobalOptions();
        $command .= $this->getCLICommandName();
        $command .= $this->getCLICommandArguments();
        $command .= $this->getCLISubjects();
        $command .= $this->getCLIPath();

        $command = preg_replace('/\\s{2,}/', ' ', $command);

        return trim($command);
    }

    /**
     * get a string of CLI-formatted command arguments
     *
     * @return string The command argument string
     */
    private function getCLICommandArguments(): string
    {
        $command = '';
        $combinedArguments = array_merge($this->globalCommandArguments, $this->commandArguments);
        if (count($combinedArguments) > 0) {
            $command .= ' ' . implode(' ', array_map('escapeshellarg', $combinedArguments));
        }

        return $command;
    }

    /**
     * get a string of CLI-formatted command name
     *
     * @return string The command name string
     */
    private function getCLICommandName(): string
    {
        return ' ' . $this->commandName;
    }

    /**
     * get a string of CLI-formatted configs
     *
     * @return string The config string
     */
    private function getCLIConfigs(): string
    {
        $command = '';
        $combinedConfigs = array_merge($this->globalConfigs, $this->configs);
        if (count($combinedConfigs) > 0) {
            foreach ($combinedConfigs as $config => $value) {
                $command .= sprintf(
                    ' %s %s=%s',
                    escapeshellarg('-c'),
                    escapeshellarg($config),
                    escapeshellarg($value)
                );
            }
        }

        return $command;
    }

    /**
     * get a string of CLI-formatted global options
     *
     * @return string The global options string
     */
    private function getCLIGlobalOptions(): string
    {
        $command = '';
        if (count($this->globalOptions) > 0) {
            foreach ($this->globalOptions as $name => $value) {
                $command .= sprintf(' %s=%s', escapeshellarg($name), escapeshellarg($value));
            }
        }

        return $command;
    }

    /**
     * get a string of CLI-formatted path
     *
     * @return string The path string
     */
    private function getCLIPath(): string
    {
        $command = '';
        if (!is_null($this->path)) {
            $command .= sprintf(' -- %s', escapeshellarg($this->path));
        }

        return $command;
    }

    /**
     * get a string of CLI-formatted subjects
     *
     * @throws \RuntimeException
     * @return string The subjects string
     */
    private function getCLISubjects(): string
    {
        $command = '';
        if (!is_null($this->commandSubject)) {
            $command .= ' ';
            if ($this->commandSubject instanceof SubCommandCommand) {
                $command .= $this->commandSubject->getCommand();
            } elseif (is_array($this->commandSubject)) {
                $command .= implode(' ', array_map('escapeshellarg', $this->commandSubject));
            } else {
                $command .= escapeshellarg($this->commandSubject);
            }
        }
        if (!is_null($this->commandSubject2)) {
            $command .= ' ';
            if ($this->commandSubject2 instanceof SubCommandCommand) {
                $command .= $this->commandSubject2->getCommand();
            } elseif (is_array($this->commandSubject2)) {
                $command .= implode(' ', array_map('escapeshellarg', $this->commandSubject2));
            } else {
                $command .= escapeshellarg($this->commandSubject2);
            }
        }

        return $command;
    }

    /**
     * Get the version of the git binary
     *
     * @return string|null
     */
    public function getBinaryVersion(): ?string
    {
        if (is_null($this->binaryVersion)) {
            $this->binaryVersion = $this->repo->getCaller()->getBinaryVersion();
        }

        return $this->binaryVersion;
    }
}
