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

namespace GitElephant\Objects;

use GitElephant\Objects\DiffObject;

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
        $this->position = 0;

        //var_dump($lines);
        $this->parseLines($lines);
    }

    private function parseLines($lines)
    {
        // I split the diff in objects.
        // I recognize an object from the starting line "diff --git SRC/test-diffs/new-file DST/test-diffs/new-file"
        $lineNumbers = array();
        foreach($lines as $i => $line) {
            $matches = array();
            if (preg_match('/^diff --git SRC\/(.*) DST\/(.*)$/', $line, $matches)) {
                $lineNumbers[] = $i;
            }
        }

        foreach($lineNumbers as $i => $lineNum) {
            if (isset($lineNumbers[$i+1])) {
                $diffObject = new DiffObject(array_slice($lines, $lineNum, $lineNumbers[$i+1]));
            } else {
                $diffObject = new DiffObject(array_slice($lines, $lineNum));
            }
        }

        //var_dump($lineNumbers);
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
