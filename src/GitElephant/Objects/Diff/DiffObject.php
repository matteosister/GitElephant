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

use GitElephant\Utilities;
use GitElephant\Objects\Diff\DiffChunk;


/**
 * Represent a diff for a single object in the repository
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class DiffObject implements \ArrayAccess, \Countable, \Iterator
{
    const MODE_INDEX = 'index';
    const MODE_MODE = 'mode';
    const MODE_NEW_FILE = 'new_file';
    const MODE_DELETED_FILE = 'deleted_file';

    private $position;
    private $origPath;
    private $destPath;
    private $mode;
    private $chunks;

    public function __construct($lines)
    {
        $this->position = 0;
        $this->chunks = array();

        $this->findPath($lines[0]);
        $this->findMode($lines[1]);

        if ($this->mode == self::MODE_INDEX || $this->mode == self::MODE_NEW_FILE) {
            $this->findChunks(array_slice($lines, 4));
        }
    }

    public function __toString()
    {
        return $this->origPath;
    }

    private function findChunks($lines)
    {
        $arrayChunks = Utilities::preg_split_array($lines, '/^@@ -(\d+,\d+)|(\d+) \+(\d+,\d+)|(\d+) @@(.*)$/');
        foreach($arrayChunks as $chunkLines) {
            $this->chunks[] = new DiffChunk($chunkLines);
        }
    }

    private function findPath($line)
    {
        $matches = array();
        if (preg_match('/^diff --git SRC\/(.*) DST\/(.*)$/', $line, $matches)) {
            $this->origPath = $matches[1];
            $this->destPath = $matches[2];
        }
    }

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

    public function getChunks()
    {
        return $this->chunks;
    }

    public function getDestPath()
    {
        return $this->destPath;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function getOrigPath()
    {
        return $this->origPath;
    }

    // ArrayAccess interface
    public function offsetExists($offset)
    {
        return isset($this->chunks[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->chunks[$offset]) ? $this->chunks[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->chunks[] = $value;
        } else {
            $this->chunks[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->chunks[$offset]);
    }

    // Countable interface
    public function count()
    {
        return count($this->chunks);
    }

    // Iterator interface
    public function current()
    {
        return $this->chunks[$this->position];
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
        return isset($this->chunks[$this->position]);
    }

    public function rewind()
    {
        $this->position = 0;
    }
}
