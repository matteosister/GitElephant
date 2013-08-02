<?php

namespace GitElephant\Command;

use GitElephant\Command\BaseCommand,
    GitElephant\Objects\Commit;

/**
 * DiffTreeCommand
 *
 * diff-tree command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class DiffTreeCommand extends BaseCommand
{
    const DIFF_TREE_COMMAND = 'diff-tree';

    /**
     * @return DiffTreeCommand
     */
    static public function getInstance()
    {
        return new self();
    }

    /**
     * get a diff of a root commit with the empty repository
     *
     * @param \GitElephant\Objects\Commit $commit the root commit object
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    public function rootDiff(Commit $commit)
    {
        if (!$commit->isRoot()) {
            throw new \InvalidArgumentException('rootDiff method accepts only root commits');
        }
        $this->clearAll();
        $this->addCommandName(static::DIFF_TREE_COMMAND);
        $this->addCommandArgument('--cc');
        $this->addCommandArgument('--root');
        $this->addCommandArgument('--dst-prefix=DST/');
        $this->addCommandArgument('--src-prefix=SRC/');
        $this->addCommandSubject($commit);

        return $this->getCommand();
    }
}
