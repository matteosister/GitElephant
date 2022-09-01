<?php

/**
 * GitElephant - An abstraction layer for git written in PHP
 * Copyright (C) 2013  Matteo Giachino
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see [http://www.gnu.org/licenses/].
 */

namespace GitElephant\Objects\Diff;

use GitElephant\Command\Caller\CallerInterface;
use GitElephant\Command\DiffCommand;
use GitElephant\Command\DiffTreeCommand;
use GitElephant\Repository;
use GitElephant\Utilities;

/**
 * Represent a collection of diffs between two trees
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class Diff implements \ArrayAccess, \Countable, \Iterator
{
    /**
     * @var \GitElephant\Repository
     */
    private $repository;

    /**
     * the cursor position
     *
     * @var int
     */
    private $position;

    /**
     * DiffObject instances
     *
     * @var array<DiffObject>
     */
    private $diffObjects = [];

    /**
     * static generator to generate a Diff object
     *
     * @param \GitElephant\Repository                 $repository repository
     * @param null|string|\GitElephant\Objects\Commit $commit1    first commit
     * @param null|string|\GitElephant\Objects\Commit $commit2    second commit
     * @param null|string                             $path       path to consider
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @return Diff
     */
    public static function create(
        Repository $repository,
        $commit1 = null,
        $commit2 = null,
        string $path = null
    ): \GitElephant\Objects\Diff\Diff {
        $commit = new self($repository);
        $commit->createFromCommand($commit1, $commit2, $path);

        return $commit;
    }

    /**
     * Class constructor
     * bare Diff object
     *
     * @param \GitElephant\Repository $repository  repository instance
     * @param array<DiffObject>                  $diffObjects  array of diff objects
     */
    public function __construct(Repository $repository, array $diffObjects = [])
    {
        $this->position = 0;
        $this->repository = $repository;
        $this->diffObjects = $diffObjects;
    }

    /**
     * get the commit properties from command
     *
     * @param string|null$commit1 commit 1
     * @param string|null$commit2 commit 2
     * @param string|null$path    path
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\LogicException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     * @see ShowCommand::commitInfo
     */
    public function createFromCommand($commit1 = null, $commit2 = null, $path = null): void
    {
        if (null === $commit1) {
            $commit1 = $this->getRepository()->getCommit();
        }

        if (is_string($commit1)) {
            $commit1 = $this->getRepository()->getCommit($commit1);
        }

        if ($commit2 === null) {
            if ($commit1->isRoot()) {
                $command = DiffTreeCommand::getInstance($this->repository)->rootDiff($commit1);
            } else {
                $command = DiffCommand::getInstance($this->repository)->diff($commit1);
            }
        } else {
            if (is_string($commit2)) {
                $commit2 = $this->getRepository()->getCommit($commit2);
            }
            $command = DiffCommand::getInstance($this->repository)->diff($commit1, $commit2, $path);
        }

        $outputLines = $this->getCaller()->execute($command)->getOutputLines();
        $this->parseOutputLines($outputLines);
    }

    /**
     * parse the output of a git command showing a commit
     *
     * @param array $outputLines output lines
     *
     * @throws \InvalidArgumentException
     */
    private function parseOutputLines(array $outputLines): void
    {
        $this->diffObjects = [];
        $splitArray = Utilities::pregSplitArray($outputLines, '/^diff --git SRC\/(.*) DST\/(.*)$/');

        foreach ($splitArray as $diffObjectLines) {
            $this->diffObjects[] = new DiffObject($diffObjectLines);
        }
    }

    /**
     * @return \GitElephant\Command\Caller\CallerInterface
     */
    private function getCaller(): CallerInterface
    {
        return $this->getRepository()->getCaller();
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

    /**
     * ArrayAccess interface
     *
     * @param int $offset offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
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
    public function offsetGet($offset): mixed
    {
        return isset($this->diffObjects[$offset]) ? $this->diffObjects[$offset] : null;
    }

    /**
     * ArrayAccess interface
     *
     * @param int|null   $offset offset
     * @param mixed $value  value
     */
    public function offsetSet($offset, $value): void
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
    public function offsetUnset($offset): void
    {
        unset($this->diffObjects[$offset]);
    }

    /**
     * Countable interface
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->diffObjects);
    }

    /**
     * Iterator interface
     *
     * @return mixed
     */
    public function current(): mixed
    {
        return $this->diffObjects[$this->position];
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
        return isset($this->diffObjects[$this->position]);
    }

    /**
     * Iterator interface
     */
    public function rewind(): void
    {
        $this->position = 0;
    }
}
