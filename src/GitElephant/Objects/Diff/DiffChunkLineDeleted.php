<?php

namespace GitElephant\Objects\Diff;

/**
 * DiffChunkLine deleted
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class DiffChunkLineDeleted extends DiffChunkLineChanged
{
    /**
     * Class constructor
     *
     * @param int    $number  line number
     * @param string $content line content
     */
    public function __construct($number, $content)
    {
        $this->setNumber($number);
        $this->setContent($content);
        $this->setType(self::DELETED);
    }

    /**
     * Get destination line number
     *
     * @return int
     */
    public function getDestNumber()
    {
        return '';
    }
}
