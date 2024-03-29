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
use GitElephant\TestCase;
use Mockery as m;

/**
 * CloneCommandTest
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class PushCommandTest extends TestCase
{
    /**
     * set up
     */
    public function setUp(): void
    {
        $this->initRepository();
        $this->getRepository()->init(false, 'master');
        $this->addFile('test');
        $this->getRepository()->commit('test', true);
    }

    /**
     * clone url
     */
    public function testPush(): void
    {
        $pc = PushCommand::getInstance();
        $this->assertEquals("push 'origin' 'master'", $pc->push());
        $this->assertEquals("push 'github' 'master'", $pc->push('github'));
        $this->assertEquals("push 'github' 'develop'", $pc->push('github', 'develop'));
        $this->getRepository()->addRemote('test-remote', 'git@github.com:matteosister/GitElephant.git');
        $remote = m::mock('GitElephant\Objects\Remote')
            ->shouldReceive('getName')->andReturn('test-remote')->getMock();
        $this->assertEquals("push 'test-remote' 'develop'", $pc->push($remote, 'develop'));
        $branch = Branch::create($this->getRepository(), 'test-branch');
        $this->assertEquals("push 'test-remote' 'test-branch'", $pc->push($remote, $branch));
    }
}
