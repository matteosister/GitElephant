<?php

namespace GitElephant\Status;

use PhpCollection\Sequence;

/**
 * Class StatusWorkingTree
 *
 * @package GitElephant\Status
 */
class StatusWorkingTree extends Status
{
    /**
     * all files with modified status in the working tree
     *
     * @return Sequence
     */
    public function all()
    {
        return new Sequence(array_filter($this->files, function(StatusFile $statusFile) {
            return $statusFile->getWorkingTreeStatus();
        }));
    }

    /**
     * filter files by working tree status
     *
     * @param string $type
     *
     * @return Sequence
     */
    protected function filterByType($type)
    {
        if (!$this->files) {
            return new Sequence();
        }

        return new Sequence(array_filter($this->files, function(StatusFile $statusFile) use ($type) {
            return $type === $statusFile->getWorkingTreeStatus();
        }));
    }
}