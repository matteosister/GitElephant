<?php
/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package GitElephant\Objects\Diff
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

    /**
     * Class constructor
     *
     * @param array $lines diff output lines from git binary
     */
    public function __construct($lines)
    {
        $this->diffObjects = array();
        $this->position    = 0;

        $splitArray = Utilities::pregSplitArray($lines, '/^diff --git SRC\/(.*) DST\/(.*)$/');
        foreach ($splitArray as $diffObjectLines) {
            $this->diffObjects[] = new DiffObject($diffObjectLines);
        }
    }

    /**
     * ArrayAccess interface
     *
     * @param int $offset offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->diffObjects[$offset]);
    }

    /**
     * ArrayAccess interface
     *
     * @param int $offset offset
     *
     * @return null|mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->diffObjects[$offset]) ? $this->diffObjects[$offset] : null;
    }

    /**
     * ArrayAccess interface
     *
     * @param int   $offset offset
     * @param mixed $value  value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->diffObjects[] = $value;
        } else {
            $this->diffObjects[$offset] = $value;
        }
    }

    /**
     * ArrayAccess interface
     *
     * @param int $offset offset
     */
    public function offsetUnset($offset)
    {
        unset($this->diffObjects[$offset]);
    }

    /**
     * Countable interface
     *
     * @return int|void
     */
    public function count()
    {
        return count($this->diffObjects);
    }

    /**
     * Iterator interface
     *
     * @return mixed
     */
    public function current()
    {
        return $this->diffObjects[$this->position];
    }

    /**
     * Iterator interface
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Iterator interface
     *
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Iterator interface
     *
     * @return bool
     */
    public function valid()
    {
        return isset($this->diffObjects[$this->position]);
    }

    /**
     * Iterator interface
     */
    public function rewind()
    {
        $this->position = 0;
    }
}
