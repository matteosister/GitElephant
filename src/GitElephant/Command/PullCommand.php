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
use GitElephant\Objects\Remote;

/**
 * Class PullCommand
 */
class PullCommand extends BaseCommand
{
    const GIT_PULL_COMMAND = 'pull';

    /**
     * @return PullCommand
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * @param Remote|string $remote
     * @param Branch|string $branch
     *
     * @return string
     */
    public function pull($remote = null, $branch = null)
    {
        if ($remote instanceof Remote) {
            $remote = $remote->getName();
        }
        if ($branch instanceof Branch) {
            $branch = $branch->getName();
        }
        $this->clearAll();
        $this->addCommandName(self::GIT_PULL_COMMAND);
        if (!is_null($remote)) {
            $this->addCommandSubject($remote);
        }
        if (!is_null($branch)) {
            $this->addCommandSubject2($branch);
        }

        return $this->getCommand();
    }
}
