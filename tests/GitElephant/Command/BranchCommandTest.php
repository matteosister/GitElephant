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
    /**
     * setUp, called on every method
     */
    public function setUp()
    {
        $this->initRepository();
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('first commit', true);
    }

    /**
     * create test
     *
     * @covers GitElephant\Command\BranchCommand::create
     */
    public function testCreate()
    {
        $branch = new BranchCommand();
        $this->assertEquals("branch 'test'", $branch->create('test'), 'create branch command');
        $this->assertEquals(1, count($this->getRepository()->getBranches()), 'one branch in initiated git repo');
        $this->getCaller()->execute($branch->create('test'));
        $this->assertEquals(2, count($this->getRepository()->getBranches()), 'two branches after add branch command');
        $this->getCaller()->execute($branch->create('test2'));
        $this->assertEquals(3, count($this->getRepository()->getBranches()), 'three branches after add branch command');
    }

    /**
     * lists test
     *
     * @covers GitElephant\Command\BranchCommand::lists
     */
    public function testLists()
    {
        $branch = new BranchCommand();
        $this->assertEquals($branch->lists(), "branch '-v' '--no-color' '--no-abbrev'");
        $this->assertEquals($branch->lists(true), "branch '-v' '--no-color' '--no-abbrev' '-a'");
        $this->assertEquals($branch->lists(false, true), "branch '--no-color' '--no-abbrev'");
    }

    /**
     * delete test
     *
     * @covers GitElephant\Command\BranchCommand::delete
     */
    public function testDelete()
    {
        $branch = new BranchCommand();
        $this->assertEquals("branch '-d' 'test-branch'", $branch->delete('test-branch'), 'list branch command');
        $this->getCaller()->execute($branch->create('test'));
        $this->getCaller()->execute($branch->delete('test'));
        $this->assertEquals(1, count($this->getRepository()->getBranches()), 'two branches after add branch command');
    }
}
