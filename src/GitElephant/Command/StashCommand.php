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
 * Stash command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 * @author Kirk Madera <kmadera@robofirm.com>
 */
class StashCommand extends BaseCommand
{
    public const STASH_COMMAND = 'stash';

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
     *  Save your local modifications to a new stash, and run git reset --hard to revert them.
     *
     * @param string|null $message
     * @param boolean $includeUntracked
     * @param boolean $keepIndex
     *
     * @return string
     */
    public function save($message = null, $includeUntracked = false, $keepIndex = false): string
    {
        $this->clearAll();

        $this->addCommandName(self::STASH_COMMAND . ' save');

        if (!is_null($message)) {
            $this->addCommandSubject($message);
        }

        if ($includeUntracked) {
            $this->addCommandArgument('--include-untracked');
        }

        if ($keepIndex) {
            $this->addCommandArgument('--keep-index');
        }

        return $this->getCommand();
    }

    /**
     * Shows stash list
     *
     * @param array|null $options
     *
     * @return string
     */
    public function listStashes(array $options = null): string
    {
        $this->clearAll();

        $this->addCommandName(self::STASH_COMMAND . ' list');
        
        if (null !== $options) {
            $this->addCommandSubject($options);
        }

        return $this->getCommand();
    }

    /**
     * Shows details for a specific stash
     *
     * @param string|int $stash
     *
     * @return string
     */
    public function show($stash): string
    {
        $stash = $this->normalizeStashName($stash);
        $this->clearAll();
        $this->addCommandName(self::STASH_COMMAND . ' show');
        $this->addCommandSubject($stash);

        return $this->getCommand();
    }

    /**
     * Drops a stash
     *
     * @param string $stash
     *
     * @return string
     */
    public function drop($stash): string
    {
        $stash = $this->normalizeStashName($stash);
        $this->clearAll();
        $this->addCommandName(self::STASH_COMMAND . ' drop');
        $this->addCommandSubject($stash);

        return $this->getCommand();
    }

    /**
     * Applies a stash
     *
     * @param string $stash
     * @param boolean $index
     *
     * @return string
     */
    public function apply($stash, $index = false): string
    {
        $stash = $this->normalizeStashName($stash);
        $this->clearAll();
        $this->addCommandName(self::STASH_COMMAND . ' apply');
        $this->addCommandSubject($stash);
        if ($index) {
            $this->addCommandArgument('--index');
        }

        return $this->getCommand();
    }

    /**
     * Applies a stash, then removes it from the stash
     *
     * @param string $stash
     * @param boolean $index
     *
     * @return string
     */
    public function pop($stash, $index = false): string
    {
        $stash = $this->normalizeStashName($stash);
        $this->clearAll();
        $this->addCommandName(self::STASH_COMMAND . ' pop');
        $this->addCommandSubject($stash);
        if ($index) {
            $this->addCommandArgument('--index');
        }

        return $this->getCommand();
    }

    /**
     *  Creates and checks out a new branch named <branchname> starting from the commit at which the <stash> was originally created
     *
     * @param string $branch
     * @param string $stash
     *
     * @return string
     */
    public function branch($branch, $stash): string
    {
        $stash = $this->normalizeStashName($stash);
        $this->clearAll();
        $this->addCommandName(self::STASH_COMMAND . ' branch');
        $this->addCommandSubject($branch);
        $this->addCommandSubject2($stash);

        return $this->getCommand();
    }

    /**
     *  Remove all the stashed states.
     */
    public function clear(): string
    {
        $this->clearAll();
        $this->addCommandName(self::STASH_COMMAND . ' clear');

        return $this->getCommand();
    }

    /**
     * Create a stash (which is a regular commit object) and return its object name, without storing it anywhere in the
     * ref namespace.
     */
    public function create(): string
    {
        $this->clearAll();
        $this->addCommandName(self::STASH_COMMAND . ' create');

        return $this->getCommand();
    }

    /**
     * @param int|string $stash
     *
     * @return string
     */
    private function normalizeStashName($stash): string
    {
        if (0 !== strpos($stash, 'stash@{')) {
            $stash = 'stash@{' . $stash . '}';
        }

        return $stash;
    }
}
