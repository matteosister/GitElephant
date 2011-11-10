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

use GitElephant\Command\Main;
use GitElephant\Objects\TreeNode;

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

    /**
     * @covers GitElephant\Repository::__construct
     * @expectedException InvalidArgumentException
     */
    public function testRepository()
    {
        $repo = new Repository('foo');
    }

    /**
     * @expectedException RuntimeException
     * @covers GitElephant\Repository::getStatus
     */
    public function testGetStatus()
    {
        $this->assertStringStartsWith('fatal: Not a git repository', $this->getRepository()->getStatus(), 'get status should return "fatal: Not a git repository"');
    }

    /**
     * @depends testGetStatus
     * @covers GitElephant\Repository::init
     */
    public function testInit()
    {
        $this->getRepository()->init();
        $this->assertRegExp('/(.*)nothing to commit(.*)/', $this->getRepository()->getStatus(true), 'init problem, git status on an empty repo should give nothing to commit');
    }

    /**
     * @depends testInit
     * @covers GitElephant\Repository::stage
     */
    public function testStage()
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->stage();
        $this->assertRegExp('/(.*)Changes to be committed(.*)/', $this->getRepository()->getStatus(true), 'stageAll error, git status should give Changes to be committed');
    }

    /**
     * @depends testStage
     * @covers GitElephant\Repository::commit
     */
    public function testCommit()
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->stage();
        $this->getRepository()->commit('initial import');
        $this->assertRegExp('/(.*)nothing to commit(.*)/', $this->getRepository()->getStatus(true), 'commit error, git status should give nothing to commit');
    }

    /**
     * @depends testCommit
     * @covers GitElephant\Repository::getTree
     */
    public function testGetTree()
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->addFolder('test-folder');
        $this->addFile('test2', 'test-folder');

        $this->getRepository()->stage();
        $this->getRepository()->commit('initial import');

        $tree = $this->getRepository()->getTree();
        $this->assertCount(2, $tree, 'One file in the repository');
        $firstNode = $tree[0];
        $this->assertInstanceOf('GitElephant\Objects\TreeNode', $firstNode, 'array access on tree should give always a node type');
        $this->assertEquals('test', $firstNode->getFilename(), 'First repository file should be named "test"');
        $secondNode = $tree[1];
        $this->assertInstanceOf('GitElephant\Objects\TreeNode', $secondNode, 'array access on tree should give always a node type');
        $this->assertEquals(TreeNode::TYPE_TREE, $secondNode->getType(), 'second node should be of type tree');
        $subtree = $this->getRepository()->getTree($secondNode->getSha());
        $subnode = $subtree[0];
        $this->assertInstanceOf('GitElephant\Objects\TreeNode', $subnode, 'array access on tree should give always a node type');
        $this->assertEquals(TreeNode::TYPE_BLOB, $subnode->getType(), 'subnode should be of type blob');
        $this->assertEquals('test2', $subnode->getFilename(), 'subnode should be named "test2"');
    }

    /**
     * @covers GitElephant\Repository::getBranches
     */
    public function testGetBranches()
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->stage();
        $this->getRepository()->commit('initial import');
        $this->assertCount(1, $this->getRepository()->getBranches(), 'an initialized repository should have only one branch');
        $this->getRepository()->createBranch('test-branch');
        $this->assertCount(2, $this->getRepository()->getBranches(), 'two branches expected');
        $this->getRepository()->deleteBranch('test-branch');
        $this->assertCount(1, $this->getRepository()->getBranches(), 'one branch expected');
        $mainBranch = $this->getRepository()->getMainBranch();
        $this->assertInstanceOf('GitElephant\Objects\TreeBranch', $this->getRepository()->getMainBranch(), 'main branch should be an instance of TreeBranch');
        $this->assertTrue($this->getRepository()->getMainBranch()->getCurrent(), 'getCurrent on main branch should be true');
        $this->assertEquals('master', $this->getRepository()->getMainBranch()->getName(), 'main branch should be named "master"');
    }
}
