<?php
/**
 * GitElephant - An abstraction layer for git written in PHP
 * Copyright (C) 2014  John Schlick John_Schlick@hotmail.com
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
 * Class PushCommand
 */
class ResetCommand extends BaseCommand
{
    const GIT_RESET_COMMAND = 'reset';
    const OPTION_HARD = "--hard";
    const TAG_HEAD = "HEAD";

    /**
     * @return PushCommand
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * @param Remote|string $remote
     * @param Branch|string $branch
     *
     * @throws \RuntimeException
     * @return string
     */
    public function reset($option = null, $tagOrCommit = null)
    {
        $this->clearAll();
        $this->addCommandName(self::GIT_RESET_COMMAND);
        // if there is an option add it.
        if (!is_null($option)) {
            $this->addCommandSubject($option);
            if (!is_null($tagOrCommit)) {
                $this->addCommandSubject2($tagOrCommit);
            }
        }

        return $this->getCommand();
    }
}
