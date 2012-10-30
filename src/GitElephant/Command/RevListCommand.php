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

use GitElephant\Command\BaseCommand,
    GitElephant\Objects\TreeTag,
    GitElephant\Objects\Commit;

/**
 * RevList Command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class RevListCommand extends BaseCommand
{
    const GIT_REVLIST = 'rev-list';

    /**
     * @return RevListCommand
     */
    static public function getInstance()
    {
        return new self();
    }

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

    /**
     * get the commits path to the passed commit. Useful to count commits in a repo
     *
     * @param \GitElephant\Objects\Commit $commit
     *
     * @return string
     */
    public function commitPath(Commit $commit)
    {
        $this->clearAll();
        $this->addCommandName(static::GIT_REVLIST);
        $this->addCommandSubject($commit->getSha());
        return $this->getCommand();
    }
}
