<?php

namespace GitElephant\Command;

use GitElephant\Command\BaseCommand;


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
    static public function getInstance()
    {
        return new self();
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
