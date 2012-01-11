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
    public function setUp()
    {
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
     * @covers GitElephant\Repository::getObjectLog
     */
    public function testGetObjectLog()
    {
        $repo = $this->getRepository();
        $repo->init();

        $this->addFolder('test');

        $this->addFile('A.txt', 'test');
        $repo->commit('added A.txt', true);

        $this->addFile('B.txt', 'test');
        $repo->commit('added B.txt', true);

        $this->addFile('C.txt', 'test');
        $repo->commit('added C.txt', true);

        $this->addFile('D.txt', 'test');
        $repo->commit('added D.txt', true);

        $this->addFile('E.txt', 'test');
        $repo->commit('added E.txt', true);

        $tree = $repo->getTree();
        $obj = $tree[0];

        $log = $this->getRepository()->getObjectLog($obj);
        $this->assertInstanceOf('GitElephant\Objects\Log', $log);
        $this->assertEquals(1, $log->count());

        $log = $this->getRepository()->getObjectLog($obj, null, null, null);
        $this->assertEquals(5, $log->count());

        $message = $log->first()->getMessage();
        $this->assertEquals('added E.txt', $message[0]);

        $message = $log->last()->getMessage();
        $this->assertEquals('added A.txt', $message[0]);
    }

    /**
     * Test logs on different tree objects
     *
     * @covers GitElephant\Repository::getObjectLog
     */
    public function testGetObjectLogFolders()
    {
        $repo = $this->getRepository();
        $repo->init();

        $this->addFolder('A');
        $this->addFile('A1.txt', 'A');
        $repo->commit('A/A1', true);

        $this->addFile('A2.txt', 'A');
        $repo->commit('A/A2', true);

        $this->addFolder('B');
        $this->addFile('B1.txt', 'B');
        $repo->commit('B/B1', true);

        $this->addFile('B2.txt', 'B');
        $repo->commit('B/B2', true);

        $tree = $repo->getTree();

        /* @var $treeObj TreeObject */
        foreach ($tree as $treeObj) {
            $name = $treeObj->getName();
            $log = $repo->getObjectLog($treeObj, null, null, null);

            $this->assertEquals(2, $log->count());

            $i = 2;
            foreach ($log as $commit) {
                $message = $commit->getMessage();
                $this->assertEquals($name . '/' . $name . $i, $message[0]);
                --$i;
            }
        }
    }

    /**
     * Test logs on different branches
     *
     * @covers GitElephant\Repository::getObjectLog
     */
    public function testGetObjectLogBranches()
    {
        $repo = $this->getRepository();
        $repo->init();

        $this->addFolder('A');
        $this->addFile('A1.txt', 'A');
        $repo->commit('A/A1', true);

        $this->addFile('A2.txt', 'A');
        $repo->commit('A/A2', true);

        $repo->createBranch('test-branch');
        $repo->checkout('test-branch');

        $this->addFile('A3.txt', 'A');
        $repo->commit('A/A3', true);

        // master branch
        $repo->checkout('master');
        $tree = $repo->getTree();
        $dir = $tree[0];
        $log = $repo->getObjectLog($dir, null, null, null);

        $this->assertEquals(2, $log->count());

        $message = $log->first()->getMessage();
        $this->assertEquals('A/A2', $message[0]);

        // test branch
        $repo->checkout('test-branch');
        $tree = $repo->getTree();
        $dir = $tree[0];
        $log = $repo->getObjectLog($dir, null, null, null);

        $this->assertEquals(3, $log->count());

        $message = $log->first()->getMessage();
        $this->assertEquals('A/A3', $message[0]);
    }

    /**
     * @covers GitElephant\Repository::getLog
     */
    public function testGetLog()
    {
        $this->getRepository()->init();

        for ($i = 0; $i < 50; $i++) {
            $this->addFile('test file ' . $i);
            $this->getRepository()->commit('test commit ' . $i, true);
        }

        $log = $this->getRepository()->getLog();
        $this->assertInstanceOf('GitElephant\Objects\Log', $this->getRepository()->getLog());
        $this->assertEquals(15, $log->count());
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
    }
}
