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
     * @param bool $all lists all remotes
     *
     * @return string the command
     */
    public function lists($all = false)
    {
        $this->clearAll();
        $this->addCommandName(self::BRANCH_COMMAND);
        $this->addCommandArgument('-v');
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
     * @param string $name The branch name
     *
     * @return string
     */
    public function singleInfo($name)
    {
        $this->clearAll();
        $this->addCommandName(self::BRANCH_COMMAND);
        $this->addCommandArgument('-v');
        $this->addCommandArgument('--list');
        $this->addCommandArgument('--no-color');
        $this->addCommandArgument('--no-abbrev');
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
