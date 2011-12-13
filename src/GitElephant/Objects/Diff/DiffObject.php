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

use GitElephant\Utilities;
use GitElephant\Objects\Diff\DiffChunk;


/**
 * Represent a diff for a single object in the repository
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class DiffObject implements \ArrayAccess, \Countable, \Iterator
{
    const MODE_INDEX        = 'index';
    const MODE_MODE         = 'mode';
    const MODE_NEW_FILE     = 'new_file';
    const MODE_DELETED_FILE = 'deleted_file';

    private $position;
    private $originalPath;
    private $destinationPath;
    private $mode;
    private $chunks;

    /**
     * Class constructor
     *
     * @param array $lines output lines for the diff
     */
    public function __construct($lines)
    {
        $this->position = 0;
        $this->chunks   = array();

        $this->findPath($lines[0]);
        $this->findMode($lines[1]);

        if ($this->mode == self::MODE_INDEX || $this->mode == self::MODE_NEW_FILE) {
            $this->findChunks(array_slice($lines, 4));
        }
    }

    /**
     * toString magic method
     *
     * @return mixed
     */
    public function __toString()
    {
        return $this->originalPath;
    }

    /**
     * Find the diff chunks
     *
     * @param array $lines output lines for the diff
     */
    private function findChunks($lines)
    {
        $arrayChunks = Utilities::pregSplitArray($lines, '/^@@ -(\d+,\d+)|(\d+) \+(\d+,\d+)|(\d+) @@(.*)$/');
        foreach ($arrayChunks as $chunkLines) {
            $this->chunks[] = new DiffChunk($chunkLines);
        }
    }

    /**
     * look for the path in the line
     *
     * @param string $line line content
     */
    private function findPath($line)
    {
        $matches = array();
        if (preg_match('/^diff --git SRC\/(.*) DST\/(.*)$/', $line, $matches)) {
            $this->originalPath    = $matches[1];
            $this->destinationPath = $matches[2];
        }
    }

    /**
     * find the line mode
     *
     * @param string $line line content
     */
    private function findMode($line)
    {
        if (preg_match('/^index (.*)\.\.(.*) (.*)$/', $line)) {
            $this->mode = self::MODE_INDEX;
        }
        if (preg_match('/^mode (.*)\.\.(.*) (.*)$/', $line)) {
            $this->mode = self::MODE_MODE;
        }
        if (preg_match('/^new file mode (.*)/', $line)) {
            $this->mode = self::MODE_NEW_FILE;
        }
        if (preg_match('/^deleted file mode (.*)/', $line)) {
            $this->mode = self::MODE_DELETED_FILE;
        }
    }

    /**
     * chunks getter
     *
     * @return array
     */
    public function getChunks()
    {
        return $this->chunks;
    }

    /**
     * destinationPath getter
     *
     * @return string
     */
    public function getDestinationPath()
    {
        return $this->destinationPath;
    }

    /**
     * mode getter
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * originalPath getter
     *
     * @return string
     */
    public function getOriginalPath()
    {
        return $this->originalPath;
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
        return isset($this->chunks[$offset]);
    }

    /**
     * ArrayAccess interface
     *
     * @param int $offset offset
     *
     * @return null
     */
    public function offsetGet($offset)
    {
        return isset($this->chunks[$offset]) ? $this->chunks[$offset] : null;
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
            $this->chunks[] = $value;
        } else {
            $this->chunks[$offset] = $value;
        }
    }

    /**
     * ArrayAccess interface
     *
     * @param int $offset offset
     */
    public function offsetUnset($offset)
    {
        unset($this->chunks[$offset]);
    }

    /**
     * Countable interface
     *
     * @return int
     */
    public function count()
    {
        return count($this->chunks);
    }

    /**
     * Iterator interface
     *
     * @return mixed
     */
    public function current()
    {
        return $this->chunks[$this->position];
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
        return isset($this->chunks[$this->position]);
    }

    /**
     * Iterator interface
     */
    public function rewind()
    {
        $this->position = 0;
    }
}
