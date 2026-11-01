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
use GitElephant\Objects\Commit;
use GitElephant\Objects\Tag;
use GitElephant\Objects\TreeishInterface;
use GitElephant\Repository;

/**
 * RevList Command generator
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class RevListCommand extends BaseCommand
{
    public const GIT_REVLIST = 'rev-list';

    /**
     * constructor
     *
     * @param \GitElephant\Repository $repo The repository object this command
     *                                      will interact with
     */
    public function __construct(?Repository $repo = null)
    {
        parent::__construct($repo);
    }

    /**
     * get tag commit command via rev-list
     *
     * @param \GitElephant\Objects\Tag $tag a tag instance
     *
     * @throws \RuntimeException
     */
    public function getTagCommit(Tag $tag): string
    {
        $this->clearAll();
        $this->addCommandName(static::GIT_REVLIST);
        // only the last commit
        $this->addCommandArgument('-n1');
        $this->addCommandSubject($tag->getFullRef());

        return $this->getCommand();
    }

    /**
     * get the commits path to the passed commit. Useful to count commits in a repo
     *
     * @param \GitElephant\Objects\Commit $commit commit instance
     * @param int                         $max    max count
     *
     * @throws \RuntimeException
     */
    public function commitPath(Commit $commit, $max = 1000): string
    {
        $this->clearAll();
        $this->addCommandName(static::GIT_REVLIST);
        $this->addCommandArgument(sprintf('--max-count=%s', $max));
        $this->addCommandSubject($commit->getSha());

        return $this->getCommand();
    }

    /**
     * rev-list left-right count between two branches or references
     *
     *
     * @throws \RuntimeException
     */
    public function countBetween(Branch|TreeishInterface|string $left, Branch|TreeishInterface|string $right): string
    {
        $this->clearAll();
        $this->addCommandName(static::GIT_REVLIST);
        $this->addCommandArgument('--left-right');
        $this->addCommandArgument('--count');

        $leftRef = $left instanceof Branch ? $left->getName() : ($left instanceof TreeishInterface ? $left->getSha() : $left);
        $rightRef = $right instanceof Branch ? $right->getName() : ($right instanceof TreeishInterface ? $right->getSha() : $right);

        $this->addCommandSubject(sprintf('%s...%s', $leftRef, $rightRef));

        return $this->getCommand();
    }
}
