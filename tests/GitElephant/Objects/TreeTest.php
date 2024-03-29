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

use GitElephant\Repository;
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
    /**
     * setUp
     */
    public function setUp(): void
    {
        $this->initRepository();
        $this->getRepository()->init(false, 'master');
        $this->addFolder('test');
        $this->addFile('test/1');
        $this->addFile('test/1-2');
        $this->addFolder('test/child');
        $this->addFile('test/child/2');
        $this->addFile('3');
        $this->getRepository()->commit('first', true);
    }

    /**
     * testConstructor
     */
    public function testConstructor(): void
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
        $this->assertEquals(NodeObject::TYPE_TREE, $treeObj1->getType());
        $treeObj2 = $tree[1];
        $this->assertEquals(NodeObject::TYPE_BLOB, $treeObj2->getType());
    }

    /**
     * testWithPath
     */
    public function testWithPath(): void
    {
        /** @var Tree $tree */
        $tree = $this->repository->getTree('HEAD');
        $treeObj1 = $tree[0];

        $tree = $this->repository->getTree('HEAD', $treeObj1);

        $this->assertInstanceOf('Traversable', $tree);
        $this->assertInstanceOf('Countable', $tree);
        $this->assertCount(3, $tree);

        $treeObjChild = $tree[0];

        $this->assertEquals(NodeObject::TYPE_TREE, $treeObjChild->getType());
        $tree = $this->repository->getTree('HEAD', $treeObjChild);
        $this->assertCount(1, $tree);
    }

    /**
     * testSubmodule
     */
    public function testSubmodule(): void
    {
        $tempDir = realpath(sys_get_temp_dir()) . 'gitelephant_' . md5(uniqid());
        // horrible hack because php is beautiful.
        $tempName = @tempnam($tempDir, 'gitelephant');
        $path = $tempName;
        unlink($path);
        mkdir($path);
        $repository = new Repository($path);
        $repository->init(false, 'master');
        // required for newer git versions, 
        // see e.g. https://bugs.launchpad.net/ubuntu/+source/git/+bug/1993586
        $repository->addGlobalConfig("protocol.file.allow", "always");
        $repository->addSubmodule($this->repository->getPath());
        $repository->commit('test', true);
        $tree = $repository->getTree();
        $this->assertContainsEquals('.gitmodules', $tree);
        $this->assertContainsEquals($this->repository->getHumanishName(), $tree);
        $submodule = $tree[0];
        $this->assertEquals(NodeObject::TYPE_LINK, $submodule->getType());
    }

    /**
     * testIsRoot
     */
    public function testIsRoot(): void
    {
        $this->initRepository();
        $this->getRepository()->init(false, 'master');
        $this->addFolder('test');
        $this->addFile('test/1');
        $this->getRepository()->commit('test', true);
        $this->assertTrue($this->getRepository()->getTree()->isRoot());
        $this->assertFalse($this->getRepository()->getTree('master', 'test')->isRoot());
    }

    /**
     * testGetObject
     */
    public function testGetObject(): void
    {
        $tree = $this->getRepository()->getTree();
        $this->assertNull($tree->getObject());
        $tree = $this->getRepository()->getTree('HEAD', 'test');
        $this->assertNotNull($tree->getObject());
        $this->assertEquals(NodeObject::TYPE_TREE, $tree->getObject()->getType());
        $tree = $this->getRepository()->getTree('HEAD', 'test/1');
        $this->assertEquals(NodeObject::TYPE_BLOB, $tree->getObject()->getType());
    }
}
