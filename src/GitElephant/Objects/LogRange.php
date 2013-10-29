<?php

/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package GitElephant\Objects
 *
 * Just for fun...
 */

namespace GitElephant\Objects;

use GitElephant\Objects\GitAuthor,
    GitElephant\Repository,
    GitElephant\Command\LogRangeCommand;

/**
 * Git range log abstraction object
 *
 * @author Matteo Giachino <matteog@gmail.com>
 * @author John Cartwright <jcartdev@gmail.com>
 * @author Dhaval Patel <tech.dhaval@gmail.com>
 */
class LogRange implements \ArrayAccess, \Countable, \Iterator
{
    /**
     * @var \GitElephant\Repository
     */
    private $repository;

    /**
     * the commits related to this log
     *
     * @var array
     */
    private $commits  = array();

    /**
     * the cursor position
     *
     * @var int
     */
    private $position = 0;

    /**
     * static method to generate standalone log
     *
     * @param \GitElephant\Repository $repository  repo
     * @param array                   $outputLines output lines from command.log
     *
     * @return \GitElephant\Objects\Log
     */
    static public function createFromOutputLines(Repository $repository, $outputLines)
    {
        $log = new self($repository);
        $log->parseOutputLines($outputLines);

        return $log;
    }

    /**
     * Class constructor
     *
     * @param \GitElephant\Repository $repository  repo
     * @param string                  $ref         treeish reference
     * @param null                    $path        path
     * @param int                     $limit       limit
     * @param null                    $offset      offset
     * @param boolean                 $firstParent first parent
     */
    public function __construct(Repository $repository, $refStart, $refEnd, $path = null, $limit = 15, $offset = null, $firstParent = false)
    {
        $this->repository = $repository;
        $this->createFromCommand($refStart, $refEnd, $path, $limit, $offset, $firstParent);
    }

    /**
     * get the commit properties from command
     *
     * @param string  $refStart    treeish reference
     * @param string  $refEnd      treeish reference
     * @param string  $path        path
     * @param int     $limit       limit
     * @param string  $offset      offset
     * @param boolean $firstParent first parent
     *
     * @see ShowCommand::commitInfo
     */
    private function createFromCommand($refStart, $refEnd, $path, $limit, $offset, $firstParent)
    {
        $command = LogRangeCommand::getInstance()->showLog($refStart, $refEnd, $path, $limit, $offset, $firstParent);
        $outputLines = $this->getRepository()->getCaller()->execute($command, true, $this->getRepository()->getPath())->getOutputLines();
        $this->parseOutputLines($outputLines);
    }

    private function parseOutputLines($outputLines)
    {
        $commitLines = null;
        $this->commits = array();
        foreach ($outputLines as $line) {
            if ('' == $line) {
                continue;
            }
            if (preg_match('/^commit (\w+)$/', $line) > 0) {
                if (null !== $commitLines) {
                    $this->commits[] = Commit::createFromOutputLines($this->repository, $commitLines);
                }
                $commitLines = array();
            }
            $commitLines[] = $line;
        }
        if (null !== $commitLines && count($commitLines) > 0) {
            $this->commits[] = Commit::createFromOutputLines($this->repository, $commitLines);
        }
    }

    /**
     * Get array representation
     *
     * @return array
     */
    public function toArray()
    {
        return $this->commits;
    }

    /**
     * Get the first commit
     *
     * @return Commit|null
     */
    public function first()
    {
        return $this->offsetGet(0);
    }

    /**
     * Get the last commit
     *
     * @return Commit|null
     */
    public function last()
    {
        return $this->offsetGet($this->count() - 1);
    }

    /**
     * Get commit at index
     *
     * @param int $index the commit index
     *
     * @return Commit|null
     */
    public function index($index)
    {
        return $this->offsetGet($index);
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
        return isset($this->commits[$offset]);
    }

    /**
     * ArrayAccess interface
     *
     * @param int $offset offset
     *
     * @return Commit|null
     */
    public function offsetGet($offset)
    {
        return isset($this->commits[$offset]) ? $this->commits[$offset] : null;
    }

    /**
     * ArrayAccess interface
     *
     * @param int   $offset offset
     * @param mixed $value  value
     */
    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException('Can\'t set elements on logs');
    }

    /**
     * ArrayAccess interface
     *
     * @param int $offset offset
     */
    public function offsetUnset($offset)
    {
        throw new \RuntimeException('Can\'t unset elements on logs');
    }

    /**
     * Countable interface
     *
     * @return int|void
     */
    public function count()
    {
        return count($this->commits);
    }

    /**
     * Iterator interface
     *
     * @return Commit|null
     */
    public function current()
    {
        return $this->offsetGet($this->position);
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
        return $this->offsetExists($this->position);
    }

    /**
     * Iterator interface
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Repository setter
     *
     * @param \GitElephant\Repository $repository the repository variable
     */
    public function setRepository($repository)
    {
        $this->repository = $repository;
    }

    /**
     * Repository getter
     *
     * @return \GitElephant\Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }
}
