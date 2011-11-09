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

/**
 * RepositoryTest
 *
 * Repository Test Class
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */
 
class RepositoryTest extends TestCase
{
    public function setUp() {
        $this->initRepository();
    }

    public function testInit()
    {
        $this->assertTrue($this->getRepository()->init(), 'init error');
    }

    public function testStageAll()
    {
        $this->getRepository()->init();
        $this->getCaller()->execute('touch test', false);
        $this->assertTrue($this->getRepository()->stageAll(), sprintf('stageAll error'));
    }

    public function testCommit()
    {
        $this->getRepository()->init();
        $this->getCaller()->execute('touch test', false);
        $this->getRepository()->stageAll();
        $this->assertTrue($this->getRepository()->commit('initial import'), 'commit error');
    }

    public function testGetTree()
    {
        $this->getRepository()->init();
        $this->getCaller()->execute('touch test', false);
        $this->getRepository()->stageAll();
        $this->getRepository()->commit('initial import');

        $tree = $this->getRepository()->getTree();
        $this->assertTrue(count($tree) == 1, 'One file in the repository');
        $firstNode = $tree[0];
        $this->assertEquals('test', $firstNode->getFilename(), 'First repository file is named "test"');
    }
}
