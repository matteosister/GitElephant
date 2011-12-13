<?php

/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Just for fun...
 */

namespace GitElephant\Command;

use GitElephant\Command\Caller;

/**
 * BaseCommand
 *
 * The base class for all the git commands wrappers
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class BaseCommand
{
    private $commandName;
    private $commandArguments = array();
    private $commandSubject;

    /**
     * Clear all previuos variables
     */
    public function clearAll()
    {
        $this->commandName      = null;
        $this->commandArguments = null;
        $this->commandSubject   = null;
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
     * Add a command argument
     *
     * @param string $commandArgument the command argument
     */
    protected function addCommandArgument($commandArgument)
    {
        $this->commandArguments[] = $commandArgument;
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
     * Get the current command
     *
     * @return string
     * @throws \InvalidParameterException
     */
    public function getCommand()
    {
        if ($this->commandName == null) {
            throw new \InvalidParameterException("You should pass a commandName to execute a command");
        }

        $command = $this->commandName;
        $command .= ' ';
        if (count($this->commandArguments) > 0) {
            $command .= implode(' ', array_map('escapeshellarg', $this->commandArguments));
            $command .= ' ';
        }
        if ($this->commandSubject != null) {
            $command .= $this->commandSubject;
        }

        return trim($command);
    }
}
