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
    private $commandName;

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
     * @var array|string|SubCommandCommand|null
     */
    private $commandSubject;

    /**
     * the command second subject (i.e. for branch)
     *
     * @var array|string|SubCommandCommand|null
     */
    private $commandSubject2;

    /**
     * the path
     *
     * @var string|null
     */
    private $path;

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
    public function __construct(?Repository $repo = null)
    {
        if (!is_null($repo)) {
            $this->addGlobalConfigs($repo->getGlobalConfigs());
            $this->addGlobalOptions($repo->getGlobalOptions());

            $arguments = $repo->getGlobalCommandArguments();
            foreach ($arguments as $argument) {
                $this->addGlobalCommandArgument($argument);
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
    public static function getInstance(?Repository $repo = null)
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
        if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*(?:\s+[a-z0-9]+(?:-[a-z0-9]+)*)*$/', $commandName) !== 1) {
            throw new \InvalidArgumentException(
                sprintf('Invalid git command name "%s".', $commandName)
            );
        }

        $this->commandName = $commandName;
    }

    /**
     * Get command name
     */
    protected function getCommandName(): string
    {
        return $this->commandName;
    }

    /**
     * Set Configs
     *
     * @param array $configs the config variable. i.e. { "color.status" => "false", "color.diff" => "true" }
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
     * @param array $configs the config variable. i.e. { "color.status" => "false", "color.diff" => "true" }
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
     * @param array $options a global option
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
     * @param string|null $commandArgument the command argument
     */
    protected function addGlobalCommandArgument($commandArgument): void
    {
        if ($commandArgument !== null && $commandArgument !== '') {
            $this->globalCommandArguments[] = $commandArgument;
        }
    }

    /**
     * Get all added command arguments
     */
    protected function getCommandArguments(): array
    {
        return $this->commandArguments;
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
     * Assert that a positional argument does not start with "-", preventing git
     * from interpreting it as a command-line option (CWE-88), and does not contain
     * control characters or invalid UTF-8 sequences that escapeshellarg() could strip.
     *
     * @param mixed $value
     *
     * @throws \InvalidArgumentException
     */
    protected function assertNotAnOption($value): void
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                $this->assertNotAnOption($item);
            }

            return;
        }

        if ($value instanceof \Stringable || is_int($value) || is_float($value)) {
            $value = (string) $value;
        }

        if (!is_string($value)) {
            return;
        }

        if (str_contains($value, "\0") || str_contains($value, "\r") || str_contains($value, "\n")) {
            throw new \InvalidArgumentException(
                'Invalid argument: value cannot contain null bytes or newline characters.'
            );
        }

        if (preg_match('//u', $value) !== 1) {
            throw new \InvalidArgumentException(
                'Invalid argument: value must be valid UTF-8.'
            );
        }

        if (str_starts_with($value, '-')) {
            throw new \InvalidArgumentException(
                sprintf('Invalid argument "%s": value cannot start with "-".', $value)
            );
        }

        // Guard against escapeshellarg() stripping leading non-ASCII bytes under C/POSIX locales.
        $escaped = escapeshellarg($value);
        $unquoted = strlen($escaped) >= 2 ? substr($escaped, 1, -1) : $escaped;
        if (str_starts_with($unquoted, '-')) {
            throw new \InvalidArgumentException(
                sprintf('Invalid argument "%s": escaped value cannot start with "-".', $value)
            );
        }
    }

    /**
     * Assert that a remote name or repository URL is not an option and does not
     * use a dangerous git remote-helper transport protocol such as "ext::" or "fd::".
     *
     * @param mixed $url
     *
     * @throws \InvalidArgumentException
     */
    protected function assertSafeRemoteUrl($url): void
    {
        $this->assertNotAnOption($url);

        if ($url instanceof \Stringable) {
            $url = (string) $url;
        }

        if (is_string($url) && preg_match('/^[a-zA-Z0-9+.-]+::/', $url) === 1) {
            throw new \InvalidArgumentException(
                sprintf('Invalid remote URL "%s": remote-helper transport protocols are not allowed.', $url)
            );
        }
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
            if (!is_string($option)) {
                continue;
            }

            if (array_key_exists($option, $switchOptions)) {
                $normalizedOptions[$switchOptions[$option]] = $switchOptions[$option];
            } elseif (in_array($option, $valueOptions, true)) {
                throw new \InvalidArgumentException(
                    sprintf('Option "%s" requires a value.', $option)
                );
            } elseif (preg_match('/^(--?[a-zA-Z0-9-]+)(?:=(.+)|\s+(.+))$/s', $option, $matches) === 1) {
                $optionName = $matches[1];
                if (in_array($optionName, $valueOptions, true)) {
                    if ($matches[2] !== '') {
                        $normalizedOptions[$optionName] = $option;
                    } else {
                        $optValue = $matches[3];
                        $this->assertNotAnOption($optValue);
                        $normalizedOptions[$optionName] = [$optionName, $optValue];
                    }
                }
            }
        }

        return $normalizedOptions;
    }

    /**
     * Get the current command
     *
     * @throws \RuntimeException
     */
    public function getCommand(): string
    {
        if (is_null($this->commandName)) {
            throw new \RuntimeException("You should pass a commandName to execute a command");
        }

        $parts = [
            trim($this->getCLIConfigs()),
            trim($this->getCLIGlobalOptions()),
            trim($this->getCLICommandName()),
            trim($this->getCLICommandArguments()),
            trim($this->getCLISubjects()),
            trim($this->getCLIPath()),
        ];

        return implode(' ', array_filter($parts, static fn (string $part): bool => $part !== ''));
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
            $command .= ' ' . implode(' ', array_map(escapeshellarg(...), $combinedArguments));
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
        foreach ($combinedConfigs as $config => $value) {
            $command .= sprintf(
                ' %s %s=%s',
                escapeshellarg('-c'),
                escapeshellarg($config),
                escapeshellarg($value)
            );
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
        foreach ($this->globalOptions as $name => $value) {
            $command .= sprintf(' %s=%s', escapeshellarg($name), escapeshellarg($value));
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
                $command .= implode(' ', array_map(escapeshellarg(...), $this->commandSubject));
            } else {
                $command .= escapeshellarg($this->commandSubject);
            }
        }
        if (!is_null($this->commandSubject2)) {
            $command .= ' ';
            if ($this->commandSubject2 instanceof SubCommandCommand) {
                $command .= $this->commandSubject2->getCommand();
            } elseif (is_array($this->commandSubject2)) {
                $command .= implode(' ', array_map(escapeshellarg(...), $this->commandSubject2));
            } else {
                $command .= escapeshellarg($this->commandSubject2);
            }
        }

        return $command;
    }

    /**
     * Get the version of the git binary
     */
    public function getBinaryVersion(): ?string
    {
        $this->binaryVersion ??= $this->repo->getCaller()->getBinaryVersion();

        return $this->binaryVersion;
    }
}
