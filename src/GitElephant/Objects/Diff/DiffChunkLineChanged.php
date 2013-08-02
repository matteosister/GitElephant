<?php

namespace GitElephant\Objects\Diff;

/**
 * A changed line in the DiffChunk
 *
 * @author Mathias Geat <mathias@ailoo.net>
 */
abstract class DiffChunkLineChanged extends DiffChunkLine
{
    /**
     * Line number
     *
     * @var int
     */
    protected $number;

    /**
     * Set line number
     *
     * @param int $number line number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * Get line number
     *
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Get origin line number
     *
     * @return int
     */
    public function getOriginNumber()
    {
        return $this->getNumber();
    }

    /**
     * Get destination line number
     *
     * @return int
     */
    public function getDestNumber()
    {
        return $this->getNumber();
    }
}
