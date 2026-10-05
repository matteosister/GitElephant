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

namespace GitElephant\Status;

use GitElephant\Command\MainCommand;
use GitElephant\Repository;
use GitElephant\Sequence\Sequence;

/**
 * Class Status
 *
 * @package GitElephant\Status
 */
class Status
{
    /**
     * @var array<StatusFile>
     */
    protected $files;

    /**
     * Private constructor in order to follow the singleton pattern
     *
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\Process\Exception\RuntimeException
     */
    private function __construct(private readonly Repository $repository)
    {
        $this->files = [];
        $this->createFromCommand();
    }

    /**
     * @return \GitElephant\Status\Status
     */
    public static function get(Repository $repository)
    {
        return new static($repository);
    }

    /**
     * create from git command
     */
    private function createFromCommand(): void
    {
        $command = MainCommand::getInstance($this->repository)->status(true);
        $lines = $this->repository->getCaller()->execute($command)->getOutputLines(true);
        $this->parseOutputLines($lines);
    }

    /**
     * all files
     *
     * @return Sequence<StatusFile>
     */
    public function all(): \GitElephant\Sequence\Sequence
    {
        return new Sequence($this->files);
    }

    /**
     * untracked files
     *
     * @return Sequence<StatusFile>
     */
    public function untracked(): \GitElephant\Sequence\Sequence
    {
        return $this->filterByType(StatusFile::UNTRACKED);
    }

    /**
     * modified files
     *
     * @return Sequence<StatusFile>
     */
    public function modified(): \GitElephant\Sequence\Sequence
    {
        return $this->filterByType(StatusFile::MODIFIED);
    }

    /**
     * added files
     *
     * @return Sequence<StatusFile>
     */
    public function added(): \GitElephant\Sequence\Sequence
    {
        return $this->filterByType(StatusFile::ADDED);
    }

    /**
     * deleted files
     *
     * @return Sequence<StatusFile>
     */
    public function deleted(): \GitElephant\Sequence\Sequence
    {
        return $this->filterByType(StatusFile::DELETED);
    }

    /**
     * renamed files
     *
     * @return Sequence<StatusFile>
     */
    public function renamed(): \GitElephant\Sequence\Sequence
    {
        return $this->filterByType(StatusFile::RENAMED);
    }

    /**
     * copied files
     *
     * @return Sequence<StatusFile>
     */
    public function copied(): \GitElephant\Sequence\Sequence
    {
        return $this->filterByType(StatusFile::COPIED);
    }

    /**
     * create objects from command output
     * https://www.kernel.org/pub/software/scm/git/docs/git-status.html in the output section
     *
     */
    private function parseOutputLines(array $lines): void
    {
        foreach ($lines as $line) {
            $matches = $this->splitStatusLine($line);
            if ($matches) {
                $x = $matches[1] ?? null;
                $y = $matches[2] ?? null;
                $file = $matches[3] ?? null;
                $renamedFile = $matches[5] ?? null;
                $this->files[] = StatusFile::create($x, $y, $file, $renamedFile);
            }
        }
    }

    /**
     * @return array<string>|null
     */
    protected function splitStatusLine(string $line)
    {
        preg_match('/^([MADRCU\? ])?([MADRCU\? ])?\ "?([^"]+?)"?( -> "?([^"]+?)"?)?$/', $line, $matches);
        return $matches;
    }

    /**
     * filter files status in working tree and in index status
     *
     *
     * @return Sequence<StatusFile>
     */
    protected function filterByType(string $type): \GitElephant\Sequence\Sequence
    {
        if (!$this->files) {
            return new Sequence();
        }

        return new Sequence(
            array_filter(
                $this->files,
                fn (StatusFile $statusFile) => $type === $statusFile->getWorkingTreeStatus() || $type === $statusFile->getIndexStatus()
            )
        );
    }
}
