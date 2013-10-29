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

use GitElephant\Command\BaseCommand;
use GitElephant\Objects\Branch;
use GitElephant\Objects\TreeishInterface;

/**
 * Main command generator (init, status, add, commit, checkout)
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class MainCommand extends BaseCommand
{
    const GIT_INIT     = 'init';
    const GIT_STATUS   = 'status';
    const GIT_ADD      = 'add';
    const GIT_COMMIT   = 'commit';
    const GIT_CHECKOUT = 'checkout';
    const GIT_MOVE     = 'mv';
    const GIT_REMOVE   = 'rm';
    const GIT_RESET    = 'reset';

    /**
     * @return MainCommand
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * Init the repository
     *
     * @param bool $bare
     *
     * @return Main
     */
    public function init($bare = false)
    {
        $this->clearAll();
        if ($bare) {
            $this->addCommandArgument('--bare');
        }
        $this->addCommandName(self::GIT_INIT);

        return $this->getCommand();
    }

    /**
     * Get the repository status
     *
     * @param bool $porcelain
     *
     * @return string
     */
    public function status($porcelain = false)
    {
        $this->clearAll();
        $this->addCommandName(self::GIT_STATUS);
        if ($porcelain) {
            $this->addCommandArgument('--porcelain');
        } else {
            $this->addConfigs(array('color.status' => 'false'));
        }

        return $this->getCommand();
    }

    /**
     * Add a node to the stage
     *
     * @param string $what what should be added to the repository
     *
     * @return string
     */
    public function add($what = '.')
    {
        $this->clearAll();
        $this->addCommandName(self::GIT_ADD);
        $this->addCommandSubject($what);

        return $this->getCommand();
    }

    /**
     * Remove a node from the stage and put in the working tree
     *
     * @param string $what what should be removed from the stage
     *
     * @return string
     */
    public function unstage($what)
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
     * @param string $message  the commit message
     * @param bool   $stageAll commit all changes
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    public function commit($message, $stageAll = false)
    {
        $this->clearAll();
        if (trim($message) == '' || $message == null) {
            throw new \InvalidArgumentException(sprintf('You can\'t commit whitout message'));
        }
        $this->addCommandName(self::GIT_COMMIT);

        if ($stageAll) {
            $this->addCommandArgument('-a');
        }

        $this->addCommandArgument('-m');
        $this->addCommandSubject($message);

        return $this->getCommand();
    }

    /**
     * Checkout a treeish reference
     *
     * @param string|Branch $ref the reference to checkout
     *
     * @return string
     */
    public function checkout($ref)
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
     * @throws \InvalidArgumentException
     * @return string
     */
    public function move($from, $to)
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
     * @throws \InvalidArgumentException
     * @return string
     */
    public function remove($path, $recursive, $force)
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
    protected function validatePath($path)
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
