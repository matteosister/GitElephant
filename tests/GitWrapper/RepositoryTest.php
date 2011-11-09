<?php
/*
 * This file is part of the GitWrapper package.
 *
 * (c) Matteo Giachino <matteog@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Just for fun...
 */

namespace GitWrapper;

use GitWrapper\Command\Tree\Tree;
use GitWrapper\Command\Init;

/**
 * RepositoryTest
 *
 * Repository Test Class
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
 
class RepositoryTest extends TestCase
{
    public function testInit()
    {
        $this->assertTrue($this->repository->init(), 'init error');
    }

    public function testStageAll()
    {
        $this->caller->execute('touch test', false);
        $this->assertTrue($this->repository->stageAll(), 'stageAll error');
    }

    public function testCommit()
    {
        $this->assertTrue($this->repository->commit('initial import'), 'commit error');
    }

    public function testGetTree()
    {
        $tree = $this->repository->getTree();
        $this->assertTrue(count($tree) == 1, 'One file in the repository');
        $firstNode = $tree[0];
        $this->assertEquals('test', $firstNode->getFilename(), 'First repository file is named "test"');
    }
}
