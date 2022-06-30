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

use GitElephant\Command\LogRangeCommand;
use GitElephant\Repository;

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
    private $rangeCommits = [];

    /**
     * the cursor position
     *
     * @var int
     */
    private $position = 0;

    /**
     * Class constructor
     *
     * @param \GitElephant\Repository $repository  repo
     * @param string                  $refStart    starting reference (excluded from the range)
     * @param string                  $refEnd      ending reference
     * @param string|null                   $path        path
     * @param int                     $limit       limit
     * @param int                   $offset      offset
     * @param boolean                 $firstParent first parent
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     */
    public function __construct(
        Repository $repository,
        $refStart,
        $refEnd,
        $path = null,
        int $limit = 15,
        int $offset = 0,
        bool $firstParent = false
    ) {
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
     * @param int  $offset      offset
     * @param boolean $firstParent first parent
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @see ShowCommand::commitInfo
     */
    private function createFromCommand(
        $refStart,
        $refEnd,
        $path = null,
        int $limit = 15,
        int $offset = 0,
        bool $firstParent = false
    ): void {
        $command = LogRangeCommand::getInstance($this->getRepository())->showLog(
            $refStart,
            $refEnd,
            $path,
            $limit,
            $offset,
            $firstParent
        );

        $outputLines = $this->getRepository()
            ->getCaller()
            ->execute($command, true, $this->getRepository()->getPath())
            ->getOutputLines(true);

        $this->parseOutputLines($outputLines);
    }

    private function parseOutputLines(array $outputLines): void
    {
        $commitLines = null;
        $this->rangeCommits = [];
        foreach ($outputLines as $line) {
            if (preg_match('/^commit (\w+)$/', $line) > 0) {
                if (null !== $commitLines) {
                    $this->rangeCommits[] = Commit::createFromOutputLines($this->getRepository(), $commitLines);
                }
                $commitLines = [];
            }
            $commitLines[] = $line;
        }

        if (is_array($commitLines) && count($commitLines) !== 0) {
            $this->rangeCommits[] = Commit::createFromOutputLines($this->getRepository(), $commitLines);
        }
    }

    /**
     * Get array representation
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->rangeCommits;
    }

    /**
     * Get the first commit
     *
     * @return Commit|null
     */
    public function first(): ?\GitElephant\Objects\Commit
    {
        return $this->offsetGet(0);
    }

    /**
     * Get the last commit
     *
     * @return Commit|null
     */
    public function last(): ?\GitElephant\Objects\Commit
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
    public function index(int $index): ?\GitElephant\Objects\Commit
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
    public function offsetExists($offset): bool
    {
        return isset($this->rangeCommits[$offset]);
    }

    /**
     * ArrayAccess interface
     *
     * @param int $offset offset
     *
     * @return Commit|null
     */
    public function offsetGet($offset): ?\GitElephant\Objects\Commit
    {
        return isset($this->rangeCommits[$offset]) ? $this->rangeCommits[$offset] : null;
    }

    /**
     * ArrayAccess interface
     *
     * @param int   $offset offset
     * @param mixed $value  value
     *
     * @return void
     * @throws \RuntimeException
     */
    public function offsetSet($offset, $value): void
    {
        throw new \RuntimeException('Can\'t set elements on logs');
    }

    /**
     * ArrayAccess interface
     *
     * @param int $offset offset
     *
     * @return void
     * @throws \RuntimeException
     */
    public function offsetUnset($offset): void
    {
        throw new \RuntimeException('Can\'t unset elements on logs');
    }

    /**
     * Countable interface
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->rangeCommits);
    }

    /**
     * Iterator interface
     *
     * @return Commit|null
     */
    public function current(): ?\GitElephant\Objects\Commit
    {
        return $this->offsetGet($this->position);
    }

    /**
     * Iterator interface
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Iterator interface
     *
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Iterator interface
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->offsetExists($this->position);
    }

    /**
     * Iterator interface
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Repository setter
     *
     * @param \GitElephant\Repository $repository the repository variable
     */
    public function setRepository(Repository $repository): void
    {
        $this->repository = $repository;
    }

    /**
     * Repository getter
     *
     * @return \GitElephant\Repository
     */
    public function getRepository(): \GitElephant\Repository
    {
        return $this->repository;
    }
}
