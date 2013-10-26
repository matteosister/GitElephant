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

use GitElephant\Command\BaseCommand;
use GitElephant\Objects\TreeishInterface;

/**
 * Diff command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class DiffCommand extends BaseCommand
{
    const DIFF_COMMAND = 'diff';

    /**
     * @return DiffCommand
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * build a diff command
     *
     * @param TreeishInterface      $of   the reference to diff
     * @param TreeishInterface|null $with the source reference to diff with $of, if not specified is the current HEAD
     * @param null                  $path the path to diff, if not specified the full repository
     *
     * @return string
     */
    public function diff($of, $with = null, $path = null)
    {
        $this->clearAll();
        $this->addCommandName(self::DIFF_COMMAND);
        $this->addCommandArgument('--full-index');
        $this->addCommandArgument('--no-color');
        $this->addCommandArgument('-M');
        $this->addCommandArgument('--dst-prefix=DST/');
        $this->addCommandArgument('--src-prefix=SRC/');

        $subject = '';

        if ($with == null) {
            $subject .= $of.'^..'.$of;
        } else {
            $subject .= $with.'..'.$of;
        }

        if ($path != null) {
            $this->addPath(is_string($path) ? $path : $path->getPath());
        }

        $this->addCommandSubject($subject);

        return $this->getCommand();
    }
}
