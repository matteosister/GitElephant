<?php

declare(strict_types=1);

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

/**
 * BranchTest
 *
 * Branch test
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
final class BranchCommandTest extends TestCase
{
    /**
     * setUp, called on every method
     */
    public function setUp(): void
    {
        $this->initRepository();
        $this->getRepository()->init(false, 'master');
        $this->addFile('test');
        $this->getRepository()->commit('first commit', true);
    }

    /**
     * create test
     */
    public function testCreate(): void
    {
        $branch = new BranchCommand();
        $this->assertSame("branch 'test'", $branch->create('test'), 'create branch command');
        $this->assertCount(1, $this->getRepository()->getBranches(), 'one branch in initiated git repo');
        $this->getCaller()->execute($branch->create('test'));
        $this->assertCount(2, $this->getRepository()->getBranches(), 'two branches after add branch command');
        $this->getCaller()->execute($branch->create('test2'));
        $this->assertCount(3, $this->getRepository()->getBranches(), 'three branches after add branch command');
        $this->assertSame("branch 'test' 'master'", $branch->create('test', 'master'));
    }

    /**
     * listBranches test
     */
    public function testListBranches(): void
    {
        $branch = new BranchCommand();
        $this->assertSame("branch '-v' '--no-color' '--no-abbrev'", $branch->listBranches());
        $this->assertSame("branch '-v' '--no-color' '--no-abbrev' '-a'", $branch->listBranches(true));
        $this->assertSame("branch '--no-color' '--no-abbrev'", $branch->listBranches(false, true));
    }

    /**
     * lists test
     */
    public function testLists(): void
    {
        $branch = new BranchCommand();
        $this->assertSame("branch '-v' '--no-color' '--no-abbrev'", $branch->lists());
        $this->assertSame("branch '-v' '--no-color' '--no-abbrev' '-a'", $branch->lists(true));
        $this->assertSame("branch '--no-color' '--no-abbrev'", $branch->lists(false, true));
    }

    /**
     * testSingleInfo
     */
    public function testSingleInfo(): void
    {
        $bc = new BranchCommand();
        $this->assertSame(
            "branch '-v' '--list' '--no-color' '--no-abbrev' 'master'",
            $bc->singleInfo('master')
        );
        $this->assertSame(
            "branch '-v' '--list' '--no-color' '--no-abbrev' '-a' 'master'",
            $bc->singleInfo('master', true)
        );
        $this->assertSame(
            "branch '-v' '--list' '--no-color' '--no-abbrev' '-a' '-vv' 'master'",
            $bc->singleInfo('master', true, false, true)
        );
        $this->assertSame(
            "branch '--list' '--no-color' '--no-abbrev' '-a' '-vv' 'master'",
            $bc->singleInfo('master', true, true, true)
        );
    }

    /**
     * delete test
     */
    public function testDelete(): void
    {
        $branch = new BranchCommand();
        $this->assertSame(
            "branch '-d' 'test-branch'",
            $branch->delete('test-branch'),
            'list branch command without force'
        );
        $this->assertSame(
            "branch '-D' 'test-branch'",
            $branch->delete('test-branch', true),
            'list branch command with force'
        );
    }
}
