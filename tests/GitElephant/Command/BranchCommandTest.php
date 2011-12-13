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

use GitElephant\Command\BranchCommand;
use GitElephant\TestCase;

/**
 * BranchTest
 *
 * Branch test
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
 
class BranchCommandTest extends TestCase
{
    public function setUp()
    {
        $this->initRepository();
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('first commit', true);
    }

    /**
     * @covers GitElephant\Command\BranchCommand::create
     */
    public function testCreate()
    {
        $branch = new BranchCommand();
        $this->assertEquals($branch->create('test'), "branch test", 'create branch command');
        $this->assertEquals(1, count($this->getRepository()->getBranches()), 'one branch in initiated git repo');
        $this->getCaller()->execute($branch->create('test'));
        $this->assertEquals(2, count($this->getRepository()->getBranches()), 'two branches after add branch command');
        $this->getCaller()->execute($branch->create('test2'));
        $this->assertEquals(3, count($this->getRepository()->getBranches()), 'three branches after add branch command');
    }

    /**
     * @covers GitElephant\Command\BranchCommand::lists
     */
    public function testLists()
    {
        $branch = new BranchCommand();
        $this->assertEquals($branch->lists(), "branch '-v' '--no-color' '--no-abbrev'", 'list branch command');
    }

    /**
     * @covers GitElephant\Command\BranchCommand::delete
     */
    public function testDelete()
    {
        $branch = new BranchCommand();
        $this->assertEquals($branch->delete('test-branch'), "branch '-d' test-branch", 'list branch command');
        $this->getCaller()->execute($branch->create('test'));
        $this->getCaller()->execute($branch->delete('test'));
        $this->assertEquals(1, count($this->getRepository()->getBranches()), 'two branches after add branch command');
    }
}
