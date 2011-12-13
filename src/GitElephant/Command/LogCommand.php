<?php

/**
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
use GitElephant\Objects\TreeObject;
use GitElephant\Objects\TreeBranch;

/**
 * Log command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class LogCommand extends BaseCommand
{
    const GIT_LOG = 'log';

    /**
     * build a log command
     *
     * @param \GitElephant\Objects\TreeObject      $obj    the TreeObject to get the log for
     * @param \GitElephant\Objects\TreeBranch|null $branch the branch to consider
     * @param bool                                 $last   gets only the last log
     *
     * @return string
     */
    public function showLog(TreeObject $obj, TreeBranch $branch = null, $last = true)
    {
        $this->clearAll();

        $this->addCommandName(self::GIT_LOG);
        if ($last) {
            $this->addCommandArgument('-n 1');
        }

        $subject = '';
        if (null !== $branch) {
            $subject .= $branch->getName();
        }
        $subject .= ' -- ' . $obj->getFullPath();
        $this->addCommandSubject($subject);
        return $this->getCommand();
    }
}
