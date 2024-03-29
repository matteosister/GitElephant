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

use GitElephant\Sequence\Sequence;

/**
 * Class StatusWorkingTree
 *
 * @package GitElephant\Status
 */
class StatusWorkingTree extends Status
{
    /**
     * all files with modified status in the working tree
     *
     * @return Sequence
     */
    public function all(): \GitElephant\Sequence\Sequence
    {
        return new Sequence(
            array_filter(
                $this->files,
                function (StatusFile $statusFile) {
                    $status = $statusFile->getWorkingTreeStatus();
                    return $status !== null && $status != "";
                }
            )
        );
    }

    /**
     * filter files by working tree status
     *
     * @param string $type
     *
     * @return Sequence
     */
    protected function filterByType(string $type): \GitElephant\Sequence\Sequence
    {
        if (!$this->files) {
            return new Sequence();
        }

        return new Sequence(
            array_filter(
                $this->files,
                function (StatusFile $statusFile) use ($type) {
                    return $type === $statusFile->getWorkingTreeStatus();
                }
            )
        );
    }
}
