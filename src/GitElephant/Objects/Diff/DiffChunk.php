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

use GitElephant\Objects\Diff\DiffChunkLineAdded,
GitElephant\Objects\Diff\DiffChunkLineDeleted,
GitElephant\Objects\Diff\DiffChunkLineUnchanged;


/**
 * A single portion of a file changed in a diff
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class DiffChunk implements \ArrayAccess, \Countable, \Iterator
{
    private $position;
    private $originStartLine;
    private $originEndLine;
    private $destStartLine;
    private $destEndLine;
    private $lines;

    /**
     * Class constructor
     *
     * @param array $lines output lines from git binary
     */
    public function __construct($lines)
    {
        $this->position = 0;

        $this->getLinesNumbers($lines[0]);
        $this->parseLines(array_slice($lines, 1));
    }

    /**
     * Parse lines
     *
     * @param array $lines output lines
     *
     * @throws \Exception
     */
    private function parseLines($lines)
    {
        $i = $this->destStartLine;
        foreach ($lines as $line) {
            if (preg_match('/^\+(.*)/', $line)) {
                $this->lines[] = new DiffChunkLineAdded($i, preg_replace('/\+(.*)/', '$1', $line));
                $i++;
            } else {
                if (preg_match('/^-(.*)/', $line)) {
                    $this->lines[] = new DiffChunkLineDeleted($i, preg_replace('/-(.*)/', '$1', $line));
                } else {
                    if (preg_match('/^ (.*)/', $line) || $line == '') {
                        $this->lines[] = new DiffChunkLineUnchanged($i, ltrim($line));
                        $i++;
                        } else {
                        if (preg_match('/\\ No newline at end of file/', $line)) {
                            $i++;
                        } else {
                            throw new \Exception(sprintf('GitElephant was unable to parse the line %s', $line));
                        }
                    }
                }
            }
        }
    }

    /**
     * Get line numbers
     *
     * @param string $line a single line
     */
    private function getLinesNumbers($line)
    {
        $matches = array();
        preg_match('/@@ -(.*) \+(.*) @@?(.*)/', $line, $matches);
        if (!strpos($matches[1], ',')) {
            // one line
            $this->originStartLine = $matches[1];
            $this->originEndLine   = $matches[1];
        } else {
            list($this->originStartLine, $this->originEndLine) = explode(',', $matches[1]);
        }
        if (!strpos($matches[2], ',')) {
            // one line
            $this->destStartLine = $matches[2];
            $this->destEndLine   = $matches[2];
        } else {
            list($this->destStartLine, $this->destEndLine) = explode(',', $matches[2]);
        }
    }

    /**
     * destStartLine getter
     *
     * @return int
     */
    public function getDestStartLine()
    {
        return $this->destStartLine;
    }

    /**
     * destEndLine getter
     *
     * @return int
     */
    public function getDestEndLine()
    {
        return $this->destEndLine;
    }

    /**
     * originStartLine getter
     *
     * @return int
     */
    public function getOriginStartLine()
    {
        return $this->originStartLine;
    }

    /**
     * originEndLine getter
     *
     * @return int
     */
    public function getOriginEndLine()
    {
        return $this->originEndLine;
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
        return isset($this->lines[$offset]);
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
        return isset($this->lines[$offset]) ? $this->lines[$offset] : null;
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
            $this->lines[] = $value;
        } else {
            $this->lines[$offset] = $value;
        }
    }

    /**
     * ArrayAccess interface
     *
     * @param int $offset offset
     */
    public function offsetUnset($offset)
    {
        unset($this->lines[$offset]);
    }

    /**
     * Countable interface
     *
     * @return int|void
     */
    public function count()
    {
        return count($this->lines);
    }

    /**
     * Iterator interface
     *
     * @return mixed
     */
    public function current()
    {
        return $this->lines[$this->position];
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
        return isset($this->lines[$this->position]);
    }

    /**
     * Iterator interface
     */
    public function rewind()
    {
        $this->position = 0;
    }
}
