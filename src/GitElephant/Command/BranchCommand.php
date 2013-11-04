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

/**
 * Branch command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class BranchCommand extends BaseCommand
{
    const BRANCH_COMMAND = 'branch';

    /**
     * @return BranchCommand
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * Locate branches that contain a reference
     *
     * @param string $reference reference
     *
     * @return string the command
     */
    public function contains($reference)
    {
        $this->clearAll();
        $this->addCommandName(self::BRANCH_COMMAND);
        $this->addCommandArgument('--contains');
        $this->addCommandSubject($reference);

        return $this->getCommand();
    }

    /**
     * Create a new branch
     *
     * @param string      $name       The new branch name
     * @param string|null $startPoint the new branch start point.
     *
     * @return string the command
     */
    public function create($name, $startPoint = null)
    {
        $this->clearAll();
        $this->addCommandName(self::BRANCH_COMMAND);
        $this->addCommandSubject($name);
        if (null !== $startPoint) {
            $this->addCommandSubject2($startPoint);
        }

        return $this->getCommand();
    }

    /**
     * Lists branches
     *
     * @param bool $all    lists all remotes
     * @param bool $simple list only branch names
     *
     * @return string the command
     */
    public function lists($all = false, $simple = false)
    {
        $this->clearAll();
        $this->addCommandName(self::BRANCH_COMMAND);
        if (!$simple) {
            $this->addCommandArgument('-v');
        }
        $this->addCommandArgument('--no-color');
        $this->addCommandArgument('--no-abbrev');
        if ($all) {
            $this->addCommandArgument('-a');
        }

        return $this->getCommand();
    }

    /**
     * get info about a single branch
     *
     * @param string $name    The branch name
     * @param bool   $all     lists all remotes
     * @param bool   $simple  list only branch names
     * @param bool   $verbose verbose, show also the upstream branch
     *
     * @return string
     * @deprecated there is a problem with the --list command, as it is available from git >= 1.7.8
     */
    public function singleInfo($name, $all = false, $simple = false, $verbose = false)
    {
        $this->clearAll();
        $this->addCommandName(self::BRANCH_COMMAND);
        if (!$simple) {
            $this->addCommandArgument('-v');
        }
        $this->addCommandArgument('--list');
        $this->addCommandArgument('--no-color');
        $this->addCommandArgument('--no-abbrev');
        if ($all) {
            $this->addCommandArgument('-a');
        }
        if ($verbose) {
            $this->addCommandArgument('-vv');
        }
        $this->addCommandSubject($name);

        return $this->getCommand();
    }

    /**
     * Delete a branch by its name
     *
     * @param string $name The branch to delete
     *
     * @return string the command
     */
    public function delete($name)
    {
        $this->clearAll();
        $this->addCommandName(self::BRANCH_COMMAND);
        $this->addCommandArgument('-d');
        $this->addCommandSubject($name);

        return $this->getCommand();
    }
}
