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

namespace GitElephant;

use GitElephant\Command\LsTreeCommand;
use GitElephant\TestCase;

/**
 * LsTreeCommandTest
 *
 * LsTreeCommand class tests
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
 
class LsTreeCommandTest extends TestCase
{
    /**
     * @var \GitElephant\Command\LsTreeCommand;
     */
    private $lsTreeCommand;

    public function setUp()
    {
        $this->lsTreeCommand = new LsTreeCommand();
    }

    /**
     * @covers \GitElephant\Command\LsTreeCommand
     */
    public function testTree()
    {
        $this->assertEquals("ls-tree '-r' '-t' HEAD", $this->lsTreeCommand->tree(), 'ls-tree command test');
    }

    public function testListAll()
    {
        $this->assertEquals("ls-tree HEAD", $this->lsTreeCommand->listAll(), 'ls-tree command test');
    }
}
