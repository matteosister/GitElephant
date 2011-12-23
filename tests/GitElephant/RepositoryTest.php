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

namespace GitElephant;

use GitElephant\Command\MainCommand;
use GitElephant\Objects\TreeObject;

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
     * @covers GitElephant\Repository::getPath
     */
    public function testConstruct()
    {
        $this->assertEquals($this->getRepository()->getPath(), $this->path);
    }

    /**
     * @covers GitElephant\Repository::init
     */
    public function testInit()
    {
        $this->getRepository()->init();
        $match = false;
        foreach($this->getRepository()->getStatus() as $line) {
            if (preg_match('/nothing to commit?(.*)/', $line)) {
                $match = true;
            }
        }
        $this->assertTrue($match, 'init problem, git status on an empty repo should give nothing to commit');
    }

    /**
     * @covers GitElephant\Repository::stage
     */
    public function testStage()
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->stage();
        $match = false;
        foreach($this->getRepository()->getStatus() as $line) {
            if (preg_match('/(.*)Changes to be committed(.*)/', $line)) {
                $match = true;
            }
        }
        $this->assertTrue($match, 'stageAll error, git status should give Changes to be committed');
    }

    /**
     * @covers GitElephant\Repository::commit
     */
    public function testCommit()
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->stage();
        $this->getRepository()->commit('initial import');
        $match = false;
        foreach($this->getRepository()->getStatus() as $line) {
            if (preg_match('/nothing to commit?(.*)/', $line)) {
                $match = true;
            }
        }
        $this->assertTrue($match, 'commit error, git status should give nothing to commit');
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
     * @covers GitElephant\Repository::createBranch
     */
    public function testCreateBranch()
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('foo', true);
        $this->getRepository()->createBranch('test-branch');
        $this->assertEquals(2, count($this->getRepository()->getBranches()));
    }

    /**
     * @covers GitElephant\Repository::deleteBranch
     */
    public function testDeleteBranch()
    {
        $this->getRepository()->init();
        $this->addFile('test-file');
        $this->getRepository()->commit('test', true);
        $this->getRepository()->createBranch('branch2');
        $this->assertEquals(2, count($this->getRepository()->getBranches()));
        $this->getRepository()->deleteBranch('branch2');
        $this->assertEquals(1, count($this->getRepository()->getBranches()));
    }

    /**
     * @covers GitElephant\Repository::getBranches
     * @covers GitElephant\Repository::sortBranches
     */
    public function testGetBranches()
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->stage();
        $this->getRepository()->commit('initial import', true);
        $this->assertCount(1, $this->getRepository()->getBranches(), 'an initialized repository should have only one branch');
        $this->getRepository()->createBranch('test-branch');
        $this->assertCount(2, $this->getRepository()->getBranches(), 'two branches expected');
        $branches = $this->getRepository()->getBranches();
        $this->assertEquals('master', $branches[0]->getName());
        $this->getRepository()->deleteBranch('test-branch');
        $this->assertCount(1, $this->getRepository()->getBranches(), 'one branch expected');
        $mainBranch = $this->getRepository()->getMainBranch();
        $this->assertInstanceOf('GitElephant\Objects\TreeBranch', $this->getRepository()->getMainBranch(), 'main branch should be an instance of TreeBranch');
        $this->assertTrue($this->getRepository()->getMainBranch()->getCurrent(), 'getCurrent on main branch should be true');
        $this->assertEquals('master', $this->getRepository()->getMainBranch()->getName(), 'main branch should be named "master"');
    }

    /**
     * @covers GitElephant\Repository::getMainBranch
     */
    public function testGetMainBranch()
    {
        $this->getRepository()->init();
        $this->addFile('test-file');
        $this->getRepository()->commit('test', true);
        $this->assertEquals('master', $this->getRepository()->getMainBranch()->getName());
    }
    /**
     * @covers GitElephant\Repository::getBranch
     */
    public function testGetBranch()
    {
        $this->getRepository()->init();
        $this->addFile('test-file');
        $this->getRepository()->commit('test', true);
        $this->assertInstanceOf('GitElephant\Objects\TreeBranch', $this->getRepository()->getBranch('master'));
    }

    /**
     * @covers GitElephant\Repository::getTags
     * @covers GitElephant\Repository::getTag
     * @covers GitElephant\Repository::createTag
     * @covers GitElephant\Repository::deleteTag
     */
    public function testTags()
    {
        $this->getRepository()->init();
        $this->addFile('test-file');
        $this->getRepository()->commit('test', true);
        $this->assertEquals(0, count($this->getRepository()->getTags()));
        $this->getRepository()->createTag('test-tag');
        $this->assertEquals(1, count($this->getRepository()->getTags()));
        $this->assertInstanceOf('GitElephant\Objects\TreeTag', $this->getRepository()->getTag('test-tag'));
        $this->getRepository()->deleteTag('test-tag');
        $this->assertEquals(0, count($this->getRepository()->getTags()));
        $this->getRepository()->createTag('test-tag-from-commit', $this->getRepository()->getCommit());
        $this->assertEquals(1, count($this->getRepository()->getTags()));
        $this->getRepository()->deleteTag($this->getRepository()->getTag('test-tag-from-commit'));
        $this->assertEquals(0, count($this->getRepository()->getTags()));
    }

    /**
     * @covers GitElephant\Repository::getCommit
     */
    public function testGetCommit()
    {
        $this->getRepository()->init();
        $this->addFile('test-file');
        $this->getRepository()->commit('test', true);
        $this->assertInstanceOf('GitElephant\Objects\Commit', $this->getRepository()->getCommit());
    }

    /**
     * @covers GitElephant\Repository::getLog
     */
    public function testGetLog()
    {
        $this->getRepository()->init();
        $this->addFile('test-file');
        $this->getRepository()->commit('test', true);
        $tree = $this->getRepository()->getTree();
        $obj = $tree[0];
        $this->assertInstanceOf('GitElephant\Objects\Log', $this->getRepository()->getLog($obj));
    }

    /**
     * @covers GitElephant\Repository::checkout
     */
    public function testCheckout()
    {
        $this->getRepository()->init();
        $this->addFile('test-file');
        $this->getRepository()->commit('test', true);
        $this->assertEquals('master', $this->getRepository()->getMainBranch()->getName());
        $this->getRepository()->createBranch('branch2');
        $this->getRepository()->checkout('branch2');
        $this->assertEquals('branch2', $this->getRepository()->getMainBranch()->getName());
    }

    /**
     * @covers GitElephant\Repository::getTree
     * @covers GitElephant\Objects\Tree
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
        $this->assertFalse($tree->isBlob());
        $this->assertTrue($this->getRepository()->getTree($this->getRepository()->getCommit(), 'test')->isBlob());
        $this->assertCount(2, $tree, 'One file in the repository');
        $firstNode = $tree[0];
        $this->assertInstanceOf('GitElephant\Objects\TreeObject', $firstNode, 'array access on tree should give always a node type');
        $this->assertEquals('test-folder', $firstNode->getName(), 'First repository file should be named "test"');
        $secondNode = $tree[1];
        $this->assertInstanceOf('GitElephant\Objects\TreeObject', $secondNode, 'array access on tree should give always a node type');
        $this->assertEquals(TreeObject::TYPE_BLOB, $secondNode->getType(), 'second node should be of type tree');
        $subtree = $this->getRepository()->getTree('master', 'test-folder');
        $subnode = $subtree[0];
        $this->assertInstanceOf('GitElephant\Objects\TreeObject', $subnode, 'array access on tree should give always a node type');
        $this->assertEquals(TreeObject::TYPE_BLOB, $subnode->getType(), 'subnode should be of type blob');
        $this->assertEquals('test2', $subnode->getName(), 'subnode should be named "test2"');
    }

    public function testGetDiff()
    {
        $this->getRepository()->init();
        $this->addFile('test-file');
        $this->getRepository()->commit('commit 1', true);
        $this->addFile('test-file2');
        $this->getRepository()->commit('commit 2', true);
        $this->assertInstanceOf('GitElephant\Objects\Diff\Diff', $this->getRepository()->getDiff($this->getRepository()->getCommit()));
        $this->getRepository()->createTag('v1.0');
        $this->assertInstanceOf('GitElephant\Objects\Diff\Diff', $this->getRepository()->getDiff($this->getRepository()->getTag('v1.0')));
    }
}
