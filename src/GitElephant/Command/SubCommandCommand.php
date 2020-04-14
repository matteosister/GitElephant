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
 * SubCommandCommand
 *
 * A base class that can handle subcommand parameters ordering, which differs
 * for a general command
 *
 * @package GitElephant\Command
 * @author  David Neimeyer <davidneimeyer@gmail.com>
 */
class SubCommandCommand extends BaseCommand
{
    /**
     * Subjects to a subcommand name
     *
     * @var array<SubCommandCommand|array|string>
     */
    private $orderedSubjects = [];

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
     * Clear all previous variables
     */
    public function clearAll(): void
    {
        parent::clearAll();
        $this->orderedSubjects = [];
    }

    /**
     * Add a subject to this subcommand
     *
     * @param SubCommandCommand|array|string $subject
     * @return void
     */
    protected function addCommandSubject($subject): void
    {
        $this->orderedSubjects[] = $subject;
    }

    protected function getCommandSubjects(): array
    {
        return $this->orderedSubjects;
    }

    protected function extractArguments(array $args): string
    {
        $orderArgs = [];
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
    public function getCommand(): string
    {
        $command = $this->getCommandName();

        $command .= ' ';
        $args = $this->getCommandArguments();
        if (count($args) > 0) {
            $command .= $this->extractArguments($args);
            $command .= ' ';
        }
        $subjects = $this->getCommandSubjects();
        if (!empty($subjects)) {
            $command .= implode(' ', array_map('escapeshellarg', $subjects));
        }
        $command = preg_replace('/\\s{2,}/', ' ', $command);

        return trim($command);
    }
}
