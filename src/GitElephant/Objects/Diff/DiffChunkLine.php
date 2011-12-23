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

namespace GitElephant\Objects\Diff;

/**
 * A single line in the DiffChunk
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

abstract class DiffChunkLine
{
    const UNCHANGED = "unchanged";
    const ADDED     = "added";
    const DELETED   = "deleted";

    protected $number;
    protected $type;
    protected $content;

    /**
     * toString magic method
     *
     * @return string the line content
     */
    public function __toString()
    {
        return $this->getContent();
    }

    /**
     * number setter
     *
     * @param int $number line number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * number getter
     *
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * type setter
     *
     * @param string $type line type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * type getter
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * content setter
     *
     * @param string $content line content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * content getter
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }
}
