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
 * DiffChunkLine added
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class DiffChunkLineAdded extends DiffChunkLineChanged
{
    /**
     * Class constructor
     *
     * @param int    $number  line number
     * @param string $content the content
     */
    public function __construct($number, $content)
    {
        $this->setNumber($number);
        $this->setContent($content);
        $this->setType(self::ADDED);
    }

    /**
     * Get destination line number
     *
     * @return int
     */
    public function getOriginNumber()
    {
        return '';
    }
}
