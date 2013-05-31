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
 * SubCommandCommand
 *
 * A base class that can handle subcommand parameters ordering, which differes
 * for a general command
 *
 * @package GitElephant\Command
 * @author  David Neimeyer <davidneimeyer@gmail.com>
 */

class SubCommandCommand extends BaseCommand
{

    /**
     * Subjects to a subcommand name 
     */
    private $orderedSubjects = array();

    /**
     * Clear all previuos variables
     */
    public function clearAll()
    {
        parent::clearAll();
        $this->orderedSubjects = null;
    }

    protected function addCommandSubject($subject)
    {
        $this->orderedSubjects[] = $subject;
    }

    protected function getCommandSubjects()
    {
        return ($this->orderedSubjects) ? $this->orderedSubjects : array();
    }

    protected function extractArguments($args)
    {
        $orderArgs = array();
        foreach ($args as $arg) {
            if (is_array($arg)) {
                foreach ($arg as $value) {
                    if (!is_null($value)) {
                        $orderArgs[] = escapeshellarg($value);
                    }
                }
            } else {
                $orderArgs[] = escapeshellarg($arg);
            }
        }

        return implode(' ', $orderArgs);
    }

    /**
     * Get the sub command
     *
     * @return string
     * @throws \RuntimeException
     */
    public function getCommand()
    {
        $command = $this->getCommandName();

        if ($command == null) {
            throw new \RuntimeException("commandName must be specified to build a subcommand");
        }

        $command .= ' ';
        $args = $this->getCommandArguments();
        if (count($args) > 0) {
            $command .= $this->extractArguments($args);
            $command .= ' ';
        }
        $subjects = $this->getCommandSubjects();
        if (count($subjects) > 0) {
            $command .= implode(' ', array_map('escapeshellarg', $subjects));
        }
        $command = preg_replace('/\\s{2,}/', ' ', $command);

        return trim($command);
    }
}

