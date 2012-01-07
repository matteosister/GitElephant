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

use GitElephant\Command\BaseCommand,
    GitElephant\Objects\TreeBranch;

/**
 * Merge command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class MergeCommand extends BaseCommand
{
    const MERGE_COMMAND = 'merge';

    /**
     * Generate a merge command
     *
     * @param \GitElephant\Objects\TreeBranch $with the branch to merge
     * 
     * @return string
     */
    public function merge(TreeBranch $with)
    {
        $this->clearAll();

        $this->addCommandName(static::MERGE_COMMAND);
        $this->addCommandSubject($with->getFullRef());

        return $this->getCommand();
    }
}
