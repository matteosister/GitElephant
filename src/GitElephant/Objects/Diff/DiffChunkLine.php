<?php
/*
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
 * DiffChunkLine
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


    public function setNumber($number)
    {
        $this->number = $number;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent($indent = true)
    {
        return $this->content;
    }

    public function indentation()
    {
        $count   = 0;
        $content = $this->content;
        while (preg_match('/\t| /', substr($content, 0, 1))) {
            $count++;
            $content = substr($content, 1);
        }
        return $count;
    }
}
