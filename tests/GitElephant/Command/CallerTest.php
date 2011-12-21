<?php

/**
 * This file is part of the GitElephant package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Just for fun...
 */

namespace GitElephant\Command;

use GitElephant\TestCase;
use GitElephant\Command\Caller;
use GitElephant\GitBinary;
use GitElephant\Command\MainCommand;

/**
 * CallerTest
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class CallerTest extends TestCase
{
    public function setUp()
    {
        $this->initRepository();
    }

    /**
     * @covers GitElephant\Command\Caller::__construct
     */
    public function testConstructor()
    {
        $binary = new GitBinary();
        $caller = new Caller($binary, $this->getRepository()->getPath());
        $this->assertNotEmpty($caller->execute('--version'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetError()
    {
        $binary = new GitBinary();
        $caller = new Caller($binary, $this->getRepository()->getPath());
        $mainCommand = new MainCommand();
        $caller->execute('foo');
    }

    /**
     * get output test
     */
    public function testGetOutput()
    {
        $binary = new GitBinary();
        $caller = new Caller($binary, $this->getRepository()->getPath());
        $mainCommand = new MainCommand();
        $caller->execute($mainCommand->init());
        $this->assertRegExp('/^Initialized empty Git repository in(.*)/', $caller->getOutput());
    }
}
