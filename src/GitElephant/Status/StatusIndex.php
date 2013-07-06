<?php
/**
 * User: matteo
 * Date: 06/07/13
 * Time: 22.19
 * Just for fun...
 */


namespace GitElephant\Status;

use PhpCollection\Sequence;

/**
 * Class StatusIndex
 *
 * @package GitElephant\Status
 */
class StatusIndex extends Status
{
    /**
     * @return Sequence
     */
    public function untracked()
    {
        return new Sequence();
    }

    /**
     * all files with modified status in the index
     *
     * @return Sequence
     */
    public function all()
    {
        return new Sequence(array_filter($this->files, function(StatusFile $statusFile) {
            return $statusFile->getIndexStatus();
        }));
    }

    /**
     * filter files by index status
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
            return $type === $statusFile->getIndexStatus();
        }));
    }
}