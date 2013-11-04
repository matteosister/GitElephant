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

namespace GitElephant\Command;

use GitElephant\Objects\Branch;

/**
 * Merge command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class MergeCommand extends BaseCommand
{
    const MERGE_COMMAND = 'merge';

    /**
     * @return MergeCommand
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * Generate a merge command
     *
     * @param \GitElephant\Objects\Branch $with the branch to merge
     *
     * @return string
     */
    public function merge(Branch $with)
    {
        $this->clearAll();
        $this->addCommandName(static::MERGE_COMMAND);
        $this->addCommandSubject($with->getFullRef());

        return $this->getCommand();
    }
}
