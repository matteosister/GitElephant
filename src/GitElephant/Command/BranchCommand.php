<?php

/*
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Just for fun...
 */

namespace GitElephant\Command;

use GitElephant\Command\BaseCommand;


/**
 * Branch
 *
 *
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class BranchCommand extends BaseCommand
{
    /**
     * Create a new branch
     *
     * @param string $name The new branch name
     * @param string|null $startPoint the new branch start point.
     * @return string the command
     */
    public function create($name, $startPoint = null)
    {
        $this->clearAll();
        $this->addCommandName('branch');
        $subject = $startPoint == null ? $name : $name.' '.$startPoint;
        $this->addCommandSubject($subject);
        return $this->getCommand();
    }

    /**
     * Lists branches
     *
     * @return string the command
     */
    public function lists()
    {
        $this->clearAll();
        $this->addCommandName('branch');
        $this->addCommandArgument('-v');
        $this->addCommandArgument('--no-color');
        $this->addCommandArgument('--no-abbrev');
        return $this->getCommand();
    }

    /**
     * Delete a branch by its name
     *
     * @param $name The branch to delete
     * @return string the command
     */
    public function delete($name)
    {
        $this->clearAll();
        $this->addCommandName('branch');
        $this->addCommandArgument('-d');
        $this->addCommandSubject($name);
        return $this->getCommand();
    }
}
