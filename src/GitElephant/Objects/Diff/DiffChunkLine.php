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

/**
 * A single line in the DiffChunk
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
abstract class DiffChunkLine
{
    public const UNCHANGED = "unchanged";
    public const ADDED = "added";
    public const DELETED = "deleted";

    /**
     * line type
     *
     * @var string
     */
    protected $type;

    /**
     * line content
     *
     * @var string
     */
    protected $content;

    /**
     * toString magic method
     *
     * @return string the line content
     */
    public function __toString(): string
    {
        return $this->getContent();
    }

    /**
     * type setter
     *
     * @param string $type line type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * type getter
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * content setter
     *
     * @param string $content line content
     */
    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    /**
     * content getter
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}
