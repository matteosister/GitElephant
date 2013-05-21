<?php
/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package GitElephant\Command
 *
 * Just for fun...
 */

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

        $command = $this->commandName;
        $command .= ' ';
        if (count($this->commandArguments) > 0) {
            $command .= implode(' ', array_map('escapeshellarg', $this->commandArguments));
            $command .= ' ';
        }
        if (null !== $this->commandSubject) {
            $command .= escapeshellarg($this->commandSubject);
        }
        if (null !== $this->commandSubject2) {
            $command .= ' '.escapeshellarg($this->commandSubject2);
        }
        if (null !== $this->path) {
            $command .= sprintf(' -- %s', escapeshellarg($this->path));
        }
        $command = preg_replace('/\\s{2,}/', ' ', $command);

        return trim($command);
    }
}
