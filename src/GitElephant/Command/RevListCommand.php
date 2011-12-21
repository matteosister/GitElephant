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
GitElephant\Objects\TreeTag;

/**
 * RevList Command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class RevListCommand extends BaseCommand
{
    const GIT_REVLIST = 'rev-list';

    /**
     * get tag commit command via rev-list
     *
     * @param \GitElephant\Objects\TreeTag $tag a tag instance
     *
     * @return string
     */
    public function getTagCommit(TreeTag $tag)
    {
        $this->clearAll();
        $this->addCommandName(static::GIT_REVLIST);
        // only the last commit
        $this->addCommandArgument('-n1');
        $this->addCommandSubject($tag->getFullRef());
        return $this->getCommand();
    }
}
