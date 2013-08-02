<?php

namespace GitElephant\Command;

use GitElephant\Command\BaseCommand,
    GitElephant\Objects\Tag,
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
     * @param \GitElephant\Objects\Tag $tag a tag instance
     *
     * @return string
     */
    public function getTagCommit(Tag $tag)
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
     * @param \GitElephant\Objects\Commit $commit commit instance
     * @param int                         $max    max count
     *
     * @return string
     */
    public function commitPath(Commit $commit, $max = 1000)
    {
        $this->clearAll();
        $this->addCommandName(static::GIT_REVLIST);
        $this->addCommandArgument(sprintf('--max-count=%s', $max));
        $this->addCommandSubject($commit->getSha());

        return $this->getCommand();
    }
}
