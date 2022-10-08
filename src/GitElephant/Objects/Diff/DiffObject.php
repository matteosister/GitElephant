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

use GitElephant\Utilities;

/**
 * Represent a diff for a single object in the repository
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class DiffObject implements \ArrayAccess, \Countable, \Iterator
{
    public const MODE_INDEX = 'index';
    public const MODE_MODE = 'mode';
    public const MODE_NEW_FILE = 'new_file';
    public const MODE_DELETED_FILE = 'deleted_file';
    public const MODE_RENAMED = 'renamed_file';

    /**
     * the cursor position
     *
     * @var int|null
     */
    private $position;

    /**
     * the original file path for the diff object
     *
     * @var string|null
     */
    private $originalPath;

    /**
     * the destination path for the diff object
     *
     * @var string|null
     */
    private $destinationPath;

    /**
     * rename similarity index
     *
     * @var int|null
     */
    private $similarityIndex;

    /**
     * the diff mode
     *
     * @var string|null
     */
    private $mode;

    /**
     * the diff chunks
     *
     * @var array
     */
    private $chunks = [];

    /**
     * Class constructor
     *
     * @param array $lines output lines for the diff
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $lines)
    {
        $this->position = 0;
        $this->chunks = [];

        $this->findPath($lines[0]);

        $sliceIndex = 4;

        if ($this->hasPathChanged()) {
            $this->findSimilarityIndex($lines[1]);
            if (isset($lines[4]) && !empty($lines[4])) {
                $this->findMode($lines[4]);
                $sliceIndex = 7;
            } else {
                $this->mode = self::MODE_RENAMED;
            }
        } else {
            $this->findMode($lines[1]);
        }

        if ($this->mode === self::MODE_INDEX || $this->mode === self::MODE_NEW_FILE) {
            $lines = array_slice($lines, $sliceIndex);
            if (!empty($lines)) {
                $this->findChunks($lines);
            }
        }
    }

    /**
     * toString magic method
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->originalPath == null ? "" : $this->originalPath;
    }

    /**
     * Find the diff chunks
     *
     * @param array $lines output lines for the diff
     *
     * @throws \InvalidArgumentException
     */
    private function findChunks(array $lines): void
    {
        $arrayChunks = Utilities::pregSplitArray(
            $lines,
            '/^@@ -(\d+,\d+)|(\d+) \+(\d+,\d+)|(\d+) @@(.*)$/'
        );
        foreach ($arrayChunks as $chunkLines) {
            $this->chunks[] = new DiffChunk($chunkLines);
        }
    }

    /**
     * look for the path in the line
     *
     * @param string $line line content
     */
    private function findPath(string $line): void
    {
        $matches = [];
        if (preg_match('/^diff --git SRC\/(.*) DST\/(.*)$/', $line, $matches)) {
            $this->originalPath = $matches[1];
            $this->destinationPath = $matches[2];
        }
    }

    /**
     * find the line mode
     *
     * @param string $line line content
     */
    private function findMode(string $line): void
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
     * look for similarity index in the line
     *
     * @param string $line line content
     */
    private function findSimilarityIndex(string $line): void
    {
        $matches = [];
        if (preg_match('/^similarity index (.*)\%$/', $line, $matches)) {
            $this->similarityIndex = $matches[1];
        }
    }

    /**
     * chunks getter
     *
     * @return array
     */
    public function getChunks(): array
    {
        return $this->chunks;
    }

    /**
     * destinationPath getter
     *
     * @return string
     */
    public function getDestinationPath(): string
    {
        return $this->destinationPath;
    }

    /**
     * mode getter
     *
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * originalPath getter
     *
     * @return string
     */
    public function getOriginalPath(): string
    {
        return $this->originalPath;
    }

    /**
     * Check if path has changed (file was renamed)
     *
     * @return bool
     */
    public function hasPathChanged(): bool
    {
        return $this->originalPath !== $this->destinationPath;
    }

    /**
     * Get similarity index
     *
     * @return int
     * @throws \RuntimeException if not a rename
     */
    public function getSimilarityIndex(): int
    {
        if ($this->hasPathChanged()) {
            return $this->similarityIndex;
        }

        throw new \RuntimeException('Cannot get similarity index on non-renames');
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
        return isset($this->chunks[$offset]);
    }

    /**
     * ArrayAccess interface
     *
     * @param int $offset offset
     *
     * @return DiffChunk|null
     */
    public function offsetGet($offset): ?DiffChunk
    {
        return isset($this->chunks[$offset]) ? $this->chunks[$offset] : null;
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
    public function offsetUnset($offset): void
    {
        unset($this->chunks[$offset]);
    }

    /**
     * Countable interface
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->chunks);
    }

    /**
     * Iterator interface
     *
     * @return DiffChunk|null
     */
    public function current(): ?DiffChunk
    {
        return $this->chunks[$this->position];
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
        return isset($this->chunks[$this->position]);
    }

    /**
     * Iterator interface
     */
    public function rewind(): void
    {
        $this->position = 0;
    }
}
