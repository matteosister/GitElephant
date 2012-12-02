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

namespace GitElephant\Objects;

use GitElephant\TestCase;

/**
 * TreeTest
 *
 * Tree class tests
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class TreeTest extends TestCase
{
    public function setUp()
    {
        $this->initRepository();
        $this->getRepository()->init();
        $this->addFolder('test');
        $this->addFile('test/1');
        $this->addFile('test/1-2');
        $this->addFolder('test/child');
        $this->addFile('test/child/2');
        $this->addFile('3');
        $this->getRepository()->commit('first', true);
    }

    public function testConstructor()
    {
        $tree = $this->repository->getTree('HEAD');
        $this->assertInstanceOf('Traversable', $tree);
        $this->assertInstanceOf('Countable', $tree);
        $this->assertCount(2, $tree);
        $this->addFile('4');
        $this->getRepository()->commit('second', true);
        $tree = $this->repository->getTree('HEAD');
        $this->assertCount(3, $tree);
        $treeObj1 = $tree[0];
        $this->assertEquals(TreeObject::TYPE_TREE, $treeObj1->getType());
        $treeObj2 = $tree[1];
        $this->assertEquals(TreeObject::TYPE_BLOB, $treeObj2->getType());
    }

    public function testWithPath()
    {
        $tree = $this->repository->getTree('HEAD');
        $treeObj1 = $tree[0];
        $tree = $this->repository->getTree('HEAD', $treeObj1);
        $this->assertInstanceOf('Traversable', $tree);
        $this->assertInstanceOf('Countable', $tree);
        $this->assertCount(3, $tree);
        $treeObjChild = $tree[0];
        $this->assertEquals(TreeObject::TYPE_TREE, $treeObjChild->getType());
        $tree = $this->repository->getTree('HEAD', $treeObjChild);
        $this->assertCount(1, $tree);
    }
}
