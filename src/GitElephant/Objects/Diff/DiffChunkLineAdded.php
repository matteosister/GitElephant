<?php

namespace GitElephant\Objects\Diff;

/**
 * DiffChunkLine added
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class DiffChunkLineAdded extends DiffChunkLineChanged
{
    /**
     * Class constructor
     *
     * @param int    $number  line number
     * @param string $content the content
     */
    public function __construct($number, $content)
    {
        $this->setNumber($number);
        $this->setContent($content);
        $this->setType(self::ADDED);
    }

    /**
     * Get destination line number
     *
     * @return int
     */
    public function getOriginNumber()
    {
        return '';
    }
}
