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
use GitElephant\Objects\NodeObject;
use GitElephant\Objects\TreeishInterface;
use GitElephant\Repository;

/**
 * ls-tree command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class LsTreeCommand extends BaseCommand
{
    public const LS_TREE_COMMAND = 'ls-tree';

    /**
     * constructor
     *
     * @param \GitElephant\Repository $repo The repository object this command
     *                                      will interact with
     */
    public function __construct(Repository $repo = null)
    {
        parent::__construct($repo);
    }

    /**
     * build a ls-tree command
     *
     * @param string|Branch $ref The reference to build the tree from
     *
     * @throws \RuntimeException
     * @return string
     */
    public function fullTree($ref = 'HEAD'): string
    {
        $what = $ref;
        if ($ref instanceof TreeishInterface) {
            $what = $ref->getSha();
        }
        $this->clearAll();
        $this->addCommandName(self::LS_TREE_COMMAND);
        // recurse
        $this->addCommandArgument('-r');
        // show trees
        $this->addCommandArgument('-t');
        $this->addCommandArgument('-l');
        $this->addCommandSubject($what);

        return $this->getCommand();
    }

    /**
     * tree of a given path
     *
     * @param string|TreeishInterface        $ref  reference
     * @param string|NodeObject $path path
     *
     * @throws \RuntimeException
     * @return string
     */
    public function tree($ref = 'HEAD', $path = null): string
    {
        if ($path instanceof NodeObject) {
            $subjectPath = $path->getFullPath() . ($path->isTree() ? '/' : '');
        } else {
            $subjectPath = $path;
        }

        $what = $ref;
        if ($ref instanceof TreeishInterface) {
            $what = $ref->getSha();
        }
        $subject = $what;

        $this->clearAll();

        $this->addCommandName(self::LS_TREE_COMMAND);
        $this->addCommandArgument('-l');
        $this->addCommandSubject($subject);
        $this->addPath($subjectPath);

        return $this->getCommand();
    }

    /**
     * build ls-tree command that list all
     *
     * @param null|string $ref the reference to build the tree from
     *
     * @throws \RuntimeException
     * @return string
     */
    public function listAll($ref = null): string
    {
        if (is_null($ref)) {
            $ref = 'HEAD';
        }
        $this->clearAll();
        $this->addCommandName(self::LS_TREE_COMMAND);
        $this->addCommandSubject($ref);

        return $this->getCommand();
    }
}
