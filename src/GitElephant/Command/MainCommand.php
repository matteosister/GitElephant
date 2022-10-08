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

use GitElephant\Objects\Author;
use GitElephant\Objects\Branch;
use GitElephant\Objects\TreeishInterface;
use GitElephant\Repository;

/**
 * Main command generator (init, status, add, commit, checkout)
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class MainCommand extends BaseCommand
{
    public const GIT_INIT = 'init';
    public const GIT_STATUS = 'status';
    public const GIT_ADD = 'add';
    public const GIT_COMMIT = 'commit';
    public const GIT_CHECKOUT = 'checkout';
    public const GIT_MOVE = 'mv';
    public const GIT_REMOVE = 'rm';
    public const GIT_RESET = 'reset';

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
     * Init the repository
     *
     * @param bool $bare
     *
     * @throws \RuntimeException
     * @return string
     */
    public function init($bare = false, ?string $initialBranchName = null): string
    {
        $this->clearAll();
        if ($bare) {
            $this->addCommandArgument('--bare');
        }
        if ($initialBranchName) {
            $this->addCommandArgument('--initial-branch=' . $initialBranchName);
        }
        $this->addCommandName(self::GIT_INIT);

        return $this->getCommand();
    }

    /**
     * Get the repository status
     *
     * @param bool $porcelain
     *
     * @throws \RuntimeException
     * @return string
     */
    public function status($porcelain = false): string
    {
        $this->clearAll();
        $this->addCommandName(self::GIT_STATUS);
        if ($porcelain) {
            $this->addCommandArgument('--porcelain');
        } else {
            $this->addConfigs(['color.status' => 'false']);
        }

        return $this->getCommand();
    }

    /**
     * Add a node to the stage
     *
     * @param string $what what should be added to the repository
     *
     * @throws \RuntimeException
     * @return string
     */
    public function add($what = '.'): string
    {
        $this->clearAll();
        $this->addCommandName(self::GIT_ADD);
        $this->addCommandArgument('--all');
        $this->addCommandSubject($what);

        return $this->getCommand();
    }

    /**
     * Remove a node from the stage and put in the working tree
     *
     * @param string $what what should be removed from the stage
     *
     * @throws \RuntimeException
     * @return string
     */
    public function unstage($what): string
    {
        $this->clearAll();
        $this->addCommandName(self::GIT_RESET);
        $this->addCommandArgument('HEAD');
        $this->addPath($what);

        return $this->getCommand();
    }

    /**
     * Commit
     *
     * @param string|null             $message the commit message
     * @param bool                    $stageAll commit all changes
     * @param string|Author           $author override the author for this commit
     * @param bool                    $allowEmpty whether to add param `--allow-empty` to commit command
     * @param \DateTimeInterface|null $date
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return string
     */
    public function commit(
        ?string $message,
        bool $stageAll = false,
        $author = null,
        bool $allowEmpty = false,
        \DateTimeInterface $date = null
    ): string {
        $this->clearAll();

        if (trim($message) === '' || is_null($message)) {
            throw new \InvalidArgumentException(sprintf('You can\'t commit without message'));
        }
        $this->addCommandName(self::GIT_COMMIT);

        if ($stageAll) {
            $this->addCommandArgument('-a');
        }

        if ($author !== null) {
            $this->addCommandArgument('--author');
            $this->addCommandArgument($author);
        }

        if ($allowEmpty) {
            $this->addCommandArgument('--allow-empty');
        }
        
        if (null !== $date) {
            $this->addCommandArgument('--date');
            $this->addCommandArgument($date->format(\DateTimeInterface::RFC822));
        }

        $this->addCommandArgument('-m');
        $this->addCommandSubject($message);

        return $this->getCommand();
    }

    /**
     * Checkout a treeish reference
     *
     * @param string|Branch|TreeishInterface $ref the reference to checkout
     *
     * @throws \RuntimeException
     * @return string
     */
    public function checkout($ref): string
    {
        $this->clearAll();

        $what = $ref;
        if ($ref instanceof Branch) {
            $what = $ref->getName();
        } elseif ($ref instanceof TreeishInterface) {
            $what = $ref->getSha();
        }

        $this->addCommandName(self::GIT_CHECKOUT);
        $this->addCommandArgument('-q');
        $this->addCommandSubject($what);

        return $this->getCommand();
    }

    /**
     * Move a file/directory
     *
     * @param string|Object $from source path
     * @param string|Object $to   destination path
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return string
     */
    public function move($from, $to): string
    {
        $this->clearAll();

        $from = trim($from);
        if (!$this->validatePath($from)) {
            throw new \InvalidArgumentException('Invalid source path');
        }

        $to = trim($to);
        if (!$this->validatePath($to)) {
            throw new \InvalidArgumentException('Invalid destination path');
        }

        $this->addCommandName(self::GIT_MOVE);
        $this->addCommandSubject($from);
        $this->addCommandSubject2($to);

        return $this->getCommand();
    }

    /**
     * Remove a file/directory
     *
     * @param string|Object $path      the path to remove
     * @param bool          $recursive recurse
     * @param bool          $force     force
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return string
     */
    public function remove($path, $recursive, $force): string
    {
        $this->clearAll();

        $path = trim($path);
        if (!$this->validatePath($path)) {
            throw new \InvalidArgumentException('Invalid path');
        }

        $this->addCommandName(self::GIT_REMOVE);

        if ($recursive) {
            $this->addCommandArgument('-r');
        }

        if ($force) {
            $this->addCommandArgument('-f');
        }

        $this->addPath($path);

        return $this->getCommand();
    }

    /**
     * Validates a path
     *
     * @param string $path path
     *
     * @return bool
     */
    protected function validatePath($path): bool
    {
        if (empty($path)) {
            return false;
        }

        // we are always operating from root directory
        // so forbid relative paths
        if (false !== strpos($path, '..')) {
            return false;
        }

        return true;
    }
}
