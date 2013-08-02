<?php

namespace GitElephant\Command;

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
     * @var string
     */
    private $commandName;

    /**
     * an array of config options
     *
     * @var array
     */
    private $configs;

    /**
     * the command arguments
     *
     * @var array
     */
    private $commandArguments = array();

    /**
     * the command subject
     *
     * @var string
     */
    private $commandSubject;

    /**
     * the command second subject (i.e. for branch)
     *
     * @var string
     */
    private $commandSubject2;

    /**
     * the path
     *
     * @var string
     */
    private $path;

    /**
     * Clear all previuos variables
     */
    public function clearAll()
    {
        $this->commandName      = null;
        $this->commandArguments = null;
        $this->commandSubject   = null;
        $this->commandSubject2  = null;
        $this->path             = null;
    }

    /**
     * Add the command name
     *
     * @param string $commandName the command name
     */
    protected function addCommandName($commandName)
    {
        $this->commandName = $commandName;
    }

    /**
     * Get command name
     * 
     * @return string
     */
    protected function getCommandName()
    {
        return $this->commandName;
    }

    /**
     * Set Configs
     *
     * @param array $configs the config variable. i.e. { "color.status" => "false", "color.diff" => "true" }
     */
    public function addConfigs($configs)
    {
        foreach ($configs as $config => $value) {
            $this->configs[$config] = $value;
        }
    }

    /**
     * Get Configs
     *
     * @return array
     */
    public function getConfigs()
    {
        return $this->configs;
    }

    /**
     * Add a command argument
     *
     * @param string $commandArgument the command argument
     */
    protected function addCommandArgument($commandArgument)
    {
        $this->commandArguments[] = $commandArgument;
    }

    /**
     * Get all added command arguments
     * 
     * @return array
     */
    protected function getCommandArguments()
    {
        return ($this->commandArguments) ? $this->commandArguments: array();
    }

    /**
     * Add a command subject
     *
     * @param string $commandSubject the command subject
     */
    protected function addCommandSubject($commandSubject)
    {
        $this->commandSubject = $commandSubject;
    }

    /**
     * Add a second command subject
     *
     * @param string $commandSubject2 the second command subject
     */
    protected function addCommandSubject2($commandSubject2)
    {
        $this->commandSubject2 = $commandSubject2;
    }

    /**
     * Add a path to the git command
     *
     * @param string $path path
     */
    protected function addPath($path)
    {
        $this->path = $path;
    }

    /**
     * Normalize any valid option to its long name
     * an provide a structure that can be more intellegently
     * handled by other routines
     *
     * @param array $options       command options
     * @param array $switchOptions list of valid options that are switch like
     * @param array $valueOptions  list of valid options that must have a value assignment
     *
     * @return array Associative array of valid, normalized command options
     */
    public function normalizeOptions(Array $options = array(), Array $switchOptions = array(), $valueOptions = array())
    {
        $normalizedOptions = array();

        foreach ($options as $option) {
            if (array_key_exists($option, $switchOptions)) {
                $normalizedOptions[$switchOptions[$option]] = $switchOptions[$option];
            } else {
                $parts = preg_split('/([\s=])+/', $option, 2, PREG_SPLIT_DELIM_CAPTURE);
                if (count($parts)) {
                    $optionName = $parts[0];
                    if (in_array($optionName, $valueOptions)) {
                        $value = ($parts[1] == '=') ? $option : array($parts[0], $parts[2]);
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
    public function getCommand()
    {
        if ($this->commandName == null) {
            throw new \RuntimeException("You should pass a commandName to execute a command");
        }
        $command = '';
        if (count($this->configs)) {
            foreach ($this->configs as $config => $value) {
                $command .= escapeshellarg('-c');
                $command .= sprintf(' %s=%s', escapeshellarg($config), escapeshellarg($value));
            }
            $command .= ' ';
        }
        $command .= $this->commandName;
        $command .= ' ';
        if (count($this->commandArguments) > 0) {
            $command .= implode(' ', array_map('escapeshellarg', $this->commandArguments));
            $command .= ' ';
        }
        if (null !== $this->commandSubject) {
            if ($this->commandSubject instanceof SubCommandCommand) {
                $command .= $this->commandSubject->getCommand();
            } else {
                $command .= escapeshellarg($this->commandSubject);
            }
            $command .= ' ';
        }
        if (null !== $this->commandSubject2) {
            if ($this->commandSubject2 instanceof SubCommandCommand) {
                $command .= $this->commandSubject2->getCommand();
            } else {
                $command .= escapeshellarg($this->commandSubject2);
            }
            $command .= ' ';
        }
        if (null !== $this->path) {
            $command .= sprintf(' -- %s', escapeshellarg($this->path));
        }
        $command = preg_replace('/\\s{2,}/', ' ', $command);

        return trim($command);
    }
}
