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

use GitElephant\Command\DiffCommand;
use GitElephant\TestCase;

/**
 * DiffCommandTest
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
class DiffCommandTest extends TestCase
{
    /**
     * @var \GitElephant\Command\DiffCommand;
     */
    private $diffCommand;

    /**
     * set up
     */
    public function setUp()
    {
        $this->initRepository();
        $this->getRepository()->init();
        $this->addFile('foo');
        $this->getRepository()->commit('first commit', true);
        $this->diffCommand = new DiffCommand();
    }

    /**
     * diff test
     */
    public function testDiff()
    {
        $commit = $this->getRepository()->getCommit();
        $this->assertEquals(
            DiffCommand::DIFF_COMMAND . " '--full-index' '--no-color' '-M' '--dst-prefix=DST/' '--src-prefix=SRC/' 'HEAD^..HEAD'",
            $this->diffCommand->diff('HEAD')
        );
        $this->assertEquals(
            DiffCommand::DIFF_COMMAND . " '--full-index' '--no-color' '-M' '--dst-prefix=DST/' '--src-prefix=SRC/' 'branch2..HEAD' -- 'foo'",
            $this->diffCommand->diff('HEAD', 'branch2', 'foo')
        );
        $this->assertEquals(
            sprintf(
                DiffCommand::DIFF_COMMAND . " '--full-index' '--no-color' '-M' '--dst-prefix=DST/' '--src-prefix=SRC/' '%s^..%s'",
                $commit->getSha(),
                $commit->getSha()
            ),
            $this->diffCommand->diff($commit)
        );
    }
}
