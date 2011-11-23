<?php

/*
 * This file is part of the GitWrapper package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Just for fun...
 */

namespace GitElephant\Objects\Diff;

use GitElephant\Objects\Diff\DiffChunkLineAdded,
    GitElephant\Objects\Diff\DiffChunkLineDeleted,
    GitElephant\Objects\Diff\DiffChunkLineUnchanged;


/**
 * Represent a single portion of a file changed in a diff
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class DiffChunk implements \ArrayAccess, \Countable, \Iterator
{
    private $position;
    private $origin_start_line;
    private $origin_end_line;
    private $dest_start_line;
    private $dest_end_line;
    private $lines;

    public function __construct($lines)
    {
        $this->position = 0;

        $this->getLinesNumbers($lines[0]);
        $this->parseLines(array_slice($lines, 1));
    }

    private function parseLines($lines)
    {
        $i = $this->dest_start_line;
        foreach ($lines as $line) {
            if (preg_match('/^\+(.*)/', $line)) {
                $this->lines[] = new DiffChunkLineAdded($i, preg_replace('/\+(.*)/', '$1', $line));
            } else if (preg_match('/^-(.*)/', $line)) {
                $this->lines[] = new DiffChunkLineDeleted($i, preg_replace('/-(.*)/', '$1', $line));
            } else if (preg_match('/^ (.*)/', $line)) {
                $this->lines[] = new DiffChunkLineUnchanged($i, ltrim($line));
            } else {
                throw new \Exception(sprintf('GitElephant was unable to parse the line %s', $line));
            }
            $i++;
        }
    }

    private function getLinesNumbers($line) {
        $matches = array();
        preg_match('/@@ -(.*) \+(.*) @@?(.*)/', $line, $matches);
        //die();
        if (!strpos($matches[1], ',')) {
            // one line
            $this->origin_start_line = $matches[1];
            $this->origin_end_line = $matches[1];
        } else {
            list($this->origin_start_line, $this->origin_end_line) = explode(',', $matches[1]);
        }
        if (!strpos($matches[2], ',')) {
            // one line
            $this->dest_start_line = $matches[2];
            $this->dest_end_line = $matches[2];
        } else {
            list($this->dest_start_line, $this->dest_end_line) = explode(',', $matches[2]);
        }
    }

    public function getDestEndLine()
    {
        return $this->dest_end_line;
    }

    public function getDestStartLine()
    {
        return $this->dest_start_line;
    }

    public function getOriginEndLine()
    {
        return $this->origin_end_line;
    }

    public function getOriginStartLine()
    {
        return $this->origin_start_line;
    }

    // ArrayAccess interface
    public function offsetExists($offset)
    {
        return isset($this->lines[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->lines[$offset]) ? $this->lines[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->lines[] = $value;
        } else {
            $this->lines[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->lines[$offset]);
    }

    // Countable interface
    public function count()
    {
        return count($this->lines);
    }

    // Iterator interface
    public function current()
    {
        return $this->lines[$this->position];
    }

    public function next()
    {
        ++$this->position;
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        return isset($this->lines[$this->position]);
    }

    public function rewind()
    {
        $this->position = 0;
    }
}
