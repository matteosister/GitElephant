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

use GitElephant\Objects\Diff\DiffObject;
use GitElephant\Utilities;

/**
 * Represent a collection of diffs between two trees
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class Diff implements \ArrayAccess, \Countable, \Iterator
{
    private $position;
    private $diffObjects;

    public function __construct($lines)
    {
        $this->diffObjects = array();
        $this->position = 0;

        $this->parseLines($lines);
    }

    private function parseLines($lines)
    {
        $splitArray = Utilities::preg_split_array($lines, '/^diff --git SRC\/(.*) DST\/(.*)$/');
        foreach($splitArray as $diffObjectLines) {
            $this->diffObjects[] = new DiffObject($diffObjectLines);
        }
    }

    // ArrayAccess interface
    public function offsetExists($offset)
    {
        return isset($this->diffObjects[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->diffObjects[$offset]) ? $this->diffObjects[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->diffObjects[] = $value;
        } else {
            $this->diffObjects[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->diffObjects[$offset]);
    }

    // Countable interface
    public function count()
    {
        return count($this->diffObjects);
    }

    // Iterator interface
    public function current()
    {
        return $this->diffObjects[$this->position];
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
        return isset($this->diffObjects[$this->position]);
    }

    public function rewind()
    {
        $this->position = 0;
    }
}
