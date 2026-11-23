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
     * Allowlist of safe switch options for `git stash list` (which forwards options to `git log`).
     * Excludes dangerous flags such as --output, --ext-diff, and --textconv.
     *
     * @var string[]
     */
    private const ALLOWED_LIST_SWITCH_OPTIONS = [
        '-p',
        '--patch',
        '-s',
        '--no-patch',
        '--stat',
        '--name-only',
        '--name-status',
        '--oneline',
        '--abbrev-commit',
        '--no-abbrev-commit',
        '--relative-date',
        '--reverse',
        '--merges',
        '--no-merges',
    ];

    /**
     * Allowlist of safe value-taking option prefixes for `git stash list`.
     *
     * @var string[]
     */
    private const ALLOWED_LIST_VALUE_PREFIXES = [
        '--date=',
        '--pretty=',
        '--format=',
        '--max-count=',
        '--skip=',
        '--grep=',
        '--author=',
        '--since=',
        '--until=',
        '--after=',
        '--before=',
    ];

    /**
     * constructor
     *
     * @param \GitElephant\Repository $repo The repository object this command
     *                                      will interact with
     */
    public function __construct(?Repository $repo = null)
    {
        parent::__construct($repo);
    }

    /**
     *  Save your local modifications to a new stash, and run git reset --hard to revert them.
     *
     * @param string|null $message
     * @param boolean $includeUntracked
     * @param boolean $keepIndex
     */
    public function save($message = null, $includeUntracked = false, $keepIndex = false): string
    {
        $this->clearAll();

        $this->addCommandName(self::STASH_COMMAND . ' save');

        if (!is_null($message)) {
            $this->assertNotAnOption($message);
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
     *
     */
    public function listStashes(?array $options = null): string
    {
        $this->clearAll();

        $this->addCommandName(self::STASH_COMMAND . ' list');

        if (null !== $options) {
            foreach ($options as $option) {
                if (!is_string($option) || !$this->isAllowedListOption($option)) {
                    throw new \InvalidArgumentException(
                        sprintf('Invalid stash list option "%s": option is not allowed.', is_scalar($option) ? (string) $option : gettype($option))
                    );
                }
            }
            $this->addCommandSubject($options);
        }

        return $this->getCommand();
    }

    /**
     * Shows details for a specific stash
     *
     * @param string|int $stash
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
     */
    public function branch($branch, $stash): string
    {
        $this->assertNotAnOption($branch);
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
     * @throws \InvalidArgumentException
     */
    private function normalizeStashName($stash): string
    {
        if (is_int($stash)) {
            if ($stash < 0) {
                throw new \InvalidArgumentException(sprintf('Invalid stash reference "%d".', $stash));
            }

            return 'stash@{' . $stash . '}';
        }

        /** @phpstan-ignore function.alreadyNarrowedType */
        if (!is_string($stash)) {
            throw new \InvalidArgumentException('Invalid stash reference: expected non-negative integer or stash@{n}.');
        }

        if (ctype_digit($stash)) {
            return 'stash@{' . $stash . '}';
        }

        if (preg_match('/^stash@\{\d+\}$/', $stash) === 1) {
            return $stash;
        }

        throw new \InvalidArgumentException(sprintf('Invalid stash reference "%s".', $stash));
    }

    private function isAllowedListOption(string $option): bool
    {
        if (str_contains($option, "\0") || str_contains($option, "\r") || str_contains($option, "\n")) {
            return false;
        }

        if (in_array($option, self::ALLOWED_LIST_SWITCH_OPTIONS, true)) {
            return true;
        }

        if (preg_match('/^-n\d+$/', $option) === 1) {
            return true;
        }

        foreach (self::ALLOWED_LIST_VALUE_PREFIXES as $prefix) {
            if (str_starts_with($option, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
