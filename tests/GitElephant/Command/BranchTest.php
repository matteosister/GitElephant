<?php
/*
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

use GitElephant\Command\Branch;

/**
 * BranchTest
 *
 * Branch test
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
 
class BranchTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $branch = new Branch();
        $this->assertEquals($branch->create('test'), 'branch test', 'create branch command');
        $this->assertEquals($branch->create('test', 'test-from'), 'branch test test-from', 'create branch command from start point');
    }
}
