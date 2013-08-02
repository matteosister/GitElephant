<?php

namespace GitElephant\Command;

use GitElephant\Command\BaseCommand;
use GitElephant\Objects\Branch;

/**
 * Merge command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class MergeCommand extends BaseCommand
{
    const MERGE_COMMAND = 'merge';

    /**
     * @return MergeCommand
     */
    static public function getInstance()
    {
        return new self();
    }

    /**
     * Generate a merge command
     *
     * @param \GitElephant\Objects\Branch $with the branch to merge
     * 
     * @return string
     */
    public function merge(Branch $with)
    {
        $this->clearAll();
        $this->addCommandName(static::MERGE_COMMAND);
        $this->addCommandSubject($with->getFullRef());

        return $this->getCommand();
    }
}
