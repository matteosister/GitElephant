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

use GitElephant\Objects\Branch;
use GitElephant\Objects\Object;
use GitElephant\Objects\Tag;

/**
 * RepositoryTest
 *
 * Repository Test Class
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class RepositoryTest extends TestCase
{
    /**
     * setUp
     */
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

        $this->setExpectedException('GitElephant\Exception\InvalidRepositoryPathException');
        $repo = new Repository('non-existent-path');

        $repo = Repository::open($this->path);
        $this->assertInstanceOf('GitElephant\Repository', $repo);
    }

    /**
     * @covers GitElephant\Repository::init
     */
    public function testInit()
    {
        $this->getRepository()->init();
        $match = false;
        foreach ($this->getRepository()->getStatusOutput() as $line) {
            if (preg_match('/nothing to commit?(.*)/', $line)) {
                $match = true;
            }
        }
        $this->assertTrue($match, 'init problem, git status on an empty repo should give nothing to commit');
    }

    /**
     * testName
     */
    public function testName()
    {
        $this->getRepository()->setName('test-repo');
        $this->assertEquals('test-repo', $this->getRepository()->getName());
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
        foreach ($this->getRepository()->getStatusOutput() as $line) {
            if (preg_match('/(.*)Changes to be committed(.*)/', $line)) {
                $match = true;
            }
        }
        $this->assertTrue($match, 'stageAll error, git status should give Changes to be committed');
    }

    /**
     * @covers GitElephant\Repository::unstage
     */
    public function testUnstage()
    {
        $this->markTestSkipped("Repository::unstage invokes 'get reset HEAD', which does't work on a repo with no commits");

        $this->getRepository()->init();
        $this->addFile('test');
        $this->assertCount(1, $this->getRepository()->getStatus()->untracked());
        $this->assertCount(0, $this->getRepository()->getStatus()->added());
        $this->getRepository()->stage('test');
        $this->assertCount(0, $this->getRepository()->getStatus()->untracked());
        $this->assertCount(1, $this->getRepository()->getStatus()->added());
        $this->getRepository()->unstage('test');
        $this->assertCount(1, $this->getRepository()->getStatus()->untracked());
        $this->assertCount(0, $this->getRepository()->getStatus()->added());
    }

    /**
     * @covers GitElephant\Repository::commit
     * @covers GitElephant\Repository::getStatusOutput
     */
    public function testCommit()
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->stage();
        $this->getRepository()->commit('initial import');
        $match = false;
        foreach ($this->getRepository()->getStatusOutput() as $line) {
            if (preg_match('/nothing to commit?(.*)/', $line)) {
                $match = true;
            }
        }
        $this->assertTrue($match, 'commit error, git status should give nothing to commit');

        $this->getRepository()->createBranch('develop', $this->getRepository()->getCommit());
        $this->addFile('test2');
        $this->getRepository()->commit('commit 2', true, 'develop');
        $match = false;
        foreach ($this->getRepository()->getStatusOutput() as $line) {
            if (preg_match('/nothing to commit?(.*)/', $line)) {
                $match = true;
            }
        }
        $this->assertTrue($match, 'commit error, git status should give nothing to commit');
    }

    /**
     * @covers GitElephant\Repository::getStatusOutput
     */
    public function testGetStatus()
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('test commit', true);
        $output = $this->getRepository()->getStatusOutput();
        $this->assertStringEndsWith('master', $output[0]);
        $this->addFile('file2');
        $output = $this->getRepository()->getStatusOutput();
        $this->assertStringEndsWith('file2', $output[4]);
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
     */
    public function testGetBranches()
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->stage();
        $this->getRepository()->commit('initial import', true);
        $this->assertCount(
            1,
            $this->getRepository()->getBranches(),
            'an initialized repository should have only one branch'
        );
        $this->getRepository()->createBranch('test-branch');
        $this->assertCount(2, $this->getRepository()->getBranches(), 'two branches expected');
        $branches = $this->getRepository()->getBranches();
        $this->assertEquals('master', $branches[0]->getName());
        $this->getRepository()->deleteBranch('test-branch');
        $this->assertCount(1, $this->getRepository()->getBranches(), 'one branch expected');
        $mainBranch = $this->getRepository()->getMainBranch();
        $this->assertInstanceOf(
            'GitElephant\Objects\Branch',
            $this->getRepository()->getMainBranch(),
            'main branch should be an instance of Branch'
        );
        $this->assertTrue(
            $this->getRepository()->getMainBranch()->getCurrent(),
            'getCurrent on main branch should be true'
        );
        $this->assertEquals(
            'master',
            $this->getRepository()->getMainBranch()->getName(),
            'main branch should be named "master"'
        );
        $this->assertEquals(array('master'), $this->getRepository()->getBranches(true));
        $this->getRepository()->createBranch('develop');
        $this->assertEquals(array('master', 'develop'), $this->getRepository()->getBranches(true));
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
        $this->assertInstanceOf('GitElephant\Objects\Branch', $this->getRepository()->getBranch('master'));
        $this->assertNull($this->getRepository()->getBranch('a-branch-that-do-not-exists'));
    }

    /**
     * @covers GitElephant\Repository::merge
     */
    public function testMerge()
    {
        $this->getRepository()->init();
        $this->addFile('test-file');
        $this->getRepository()->commit('test', true);
        $this->assertEquals(1, count($this->getRepository()->getTree()));
        $this->getRepository()->createBranch('branch2');
        $this->getRepository()->checkout('branch2');
        $this->addFile('file2');
        $this->getRepository()->commit('test2', true);
        $this->assertEquals(2, count($this->getRepository()->getTree()));
        $this->getRepository()->checkout('master');
        $this->assertEquals(1, count($this->getRepository()->getTree()));
        $this->getRepository()->merge($this->getRepository()->getBranch('branch2'));
        $this->assertEquals(2, count($this->getRepository()->getTree()));
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
        $this->assertInstanceOf('GitElephant\Objects\Tag', $this->getRepository()->getTag('test-tag'));
        $this->getRepository()->deleteTag('test-tag');
        $this->assertEquals(0, count($this->getRepository()->getTags()));
        $this->assertNull($this->getRepository()->getTag('a-tag-that-do-not-exists'));
    }

    /**
     * test getLastTag
     */
    public function testGetLastTag()
    {
        $this->getRepository()->init();
        $this->addFile('test-file');
        $this->getRepository()->commit('test', true);
        $this->getRepository()->createTag('0.0.2');
        sleep(1);
        $this->getRepository()->createTag('0.0.4');
        sleep(1);
        $this->getRepository()->createTag('0.0.3');
        sleep(1);
        $this->getRepository()->createTag('0.0.1');
        sleep(1);
        $this->assertEquals(Tag::pick($this->getRepository(), '0.0.1'), $this->getRepository()->getLastTag());
        $this->getRepository()->createTag('0.0.05');
        $this->assertEquals(Tag::pick($this->getRepository(), '0.0.05'), $this->getRepository()->getLastTag());
        $this->getRepository()->deleteTag(Tag::pick($this->getRepository(), '0.0.05'));
        $this->assertEquals(Tag::pick($this->getRepository(), '0.0.1'), $this->getRepository()->getLastTag());
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

    public function testGetBranchOrTag()
    {
        $this->getRepository()->init();
        $this->addFile('test-file');
        $this->getRepository()->commit('test', true);
        $this->getRepository()->createBranch('branch2');
        $this->getRepository()->createTag('tag1');
        $this->assertInstanceOf('\GitElephant\Objects\Branch', $this->getRepository()->getBranchOrTag('branch2'));
        $this->assertInstanceOf('\GitElephant\Objects\Tag', $this->getRepository()->getBranchOrTag('tag1'));
        $this->assertNull($this->getRepository()->getBranchOrTag('not-exists'));
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

        $this->assertEquals('added E.txt', $log->first()->getMessage()->toString());
        $this->assertEquals('added A.txt', $log->last()->getMessage()->toString());
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

        /* @var $treeObj Object */
        foreach ($tree as $treeObj) {
            $name = $treeObj->getName();
            $log = $repo->getObjectLog($treeObj, null, null, null);

            $this->assertEquals(2, $log->count());

            $i = 2;
            foreach ($log as $commit) {
                $this->assertEquals($name . '/' . $name . $i, $commit->getMessage()->toString());
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
        $this->assertEquals('A/A2', $log->first()->getMessage()->toString());

        // test branch
        $repo->checkout('test-branch');
        $tree = $repo->getTree();
        $dir = $tree[0];
        $log = $repo->getObjectLog($dir, null, null, null);

        $this->assertEquals(3, $log->count());
        $this->assertEquals('A/A3', $log->first()->getMessage()->toString());
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
        $this->assertGreaterThan(0, $log->count());
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
        $this->assertInstanceOf(
            'GitElephant\Objects\Object',
            $firstNode,
            'array access on tree should give always a node type'
        );
        $this->assertEquals(
            'test-folder',
            $firstNode->getName(),
            'First repository file should be named "test"'
        );
        $secondNode = $tree[1];
        $this->assertInstanceOf(
            'GitElephant\Objects\Object',
            $secondNode,
            'array access on tree should give always a node type'
        );
        $this->assertEquals(
            Object::TYPE_BLOB,
            $secondNode->getType(),
            'second node should be of type tree'
        );
        $subtree = $this->getRepository()->getTree('master', 'test-folder');
        $subnode = $subtree[0];
        $this->assertInstanceOf(
            'GitElephant\Objects\Object',
            $subnode,
            'array access on tree should give always a node type'
        );
        $this->assertEquals(
            Object::TYPE_BLOB,
            $subnode->getType(),
            'subnode should be of type blob'
        );
        $this->assertEquals(
            'test2',
            $subnode->getName(),
            'subnode should be named "test2"'
        );
    }

    /**
     * @covers GitElephant\Repository::getDiff
     */
    public function testGetDiff()
    {
        $this->getRepository()->init();
        $this->addFile('test-file');
        $this->getRepository()->commit('commit 1', true);
        $commit1 = $this->getRepository()->getCommit();
        $this->assertInstanceOf('GitElephant\Objects\Diff\Diff', $this->getRepository()->getDiff($commit1));
        $this->addFile('test-file2');
        $this->getRepository()->commit('commit 2', true);
        $commit2 = $this->getRepository()->getCommit();
        $this->assertInstanceOf('GitElephant\Objects\Diff\Diff', $this->getRepository()->getDiff($commit2));
        $this->assertInstanceOf('GitElephant\Objects\Diff\Diff', $this->getRepository()->getDiff($commit2, $commit1));
        $shaHead = $this->getRepository()->getCommit();
        $this->assertInstanceOf('GitElephant\Objects\Diff\Diff', $diff = $this->getRepository()->getDiff($shaHead));
    }

    /**
     * testCloneFrom
     */
    public function testCloneFrom()
    {
        $this->initRepository(null, 0);
        $this->initRepository(null, 1);
        $remote = $this->getRepository(0);
        $remote->init();
        $this->addFile('test', null, null, $remote);
        $remote->commit('test', true);
        $local = $this->getRepository(1);
        $local->cloneFrom($remote->getPath(), '.');
        $commit = $local->getCommit();
        $this->assertEquals($remote->getCommit()->getSha(), $commit->getSha());
        $this->assertEquals($remote->getCommit()->getMessage(), $commit->getMessage());
    }

    /**
     * testOutputContent
     */
    public function testOutputContent()
    {
        $this->initRepository();
        $this->getRepository()->init();
        $this->addFile('file1', null, 'file content');
        $this->getRepository()->commit('first commit', true);
        $branch = $this->getRepository()->getBranch('master');
        $tree = $this->getRepository()->getTree($branch, 'file1');
        $treeObject = $tree->getBlob();
        $this->assertEquals(array('file content'), $this->getRepository()->outputContent($treeObject, $branch));
    }

    /**
     * testSortBranches
     */
    public function testSortBranches()
    {
        $this->initRepository();
        $this->getRepository()->init();
        $this->addFile('file1');
        $this->getRepository()->commit('first commit', true);
        $this->getRepository()->createBranch('branch1');
        $this->getRepository()->createBranch('branch2');
        $this->getRepository()->createBranch('branch3');
        $this->getRepository()->createBranch('branch4');
        $arrayNames = array();
        foreach ($this->getRepository()->getBranches() as $branch) {
            $arrayNames[] = $branch->getName();
        }
        $this->assertEquals(array('master', 'branch4', 'branch3', 'branch2', 'branch1'), $arrayNames);
    }

    /**
     * testMove
     */
    public function testMove()
    {
        $this->getRepository()->init();
        $this->addFile('foo');
        $this->getRepository()->commit('commit 1', true);
        $this->getRepository()->move('foo', 'bar');
        $status = $this->getRepository()->getStatusOutput();

        $this->assertRegExp('/(.*):    foo -> bar/', $status[4]);
    }

    /**
     * testRemove
     */
    public function testRemove()
    {
        $this->getRepository()->init();
        $this->addFile('foo');
        $this->getRepository()->commit('commit 1', true);
        $this->getRepository()->remove('foo');
        $status = $this->getRepository()->getStatusOutput();

        $this->assertRegExp('/(.*):    foo/', $status[4]);
    }

    /**
     * testCountCommits
     */
    public function testCountCommits()
    {
        $this->getRepository()->init();
        $this->addFile('foo');
        $this->getRepository()->commit('commit 1', true);
        $this->assertEquals(1, $this->getRepository()->countCommits());
        $this->addFile('foo2');
        $this->getRepository()->commit('commit 2', true);
        $this->assertEquals(2, $this->getRepository()->countCommits());
        $this->getRepository()->createBranch('new-branch');
        $this->getRepository()->checkout('new-branch');
        $this->assertEquals(2, $this->getRepository()->countCommits());
        $this->addFile('bar');
        $this->getRepository()->commit('commit 3', true);
        $this->assertEquals(3, $this->getRepository()->countCommits());
        $this->getRepository()->checkout('master');
        $this->assertEquals(2, $this->getRepository()->countCommits());
    }

    /**
     * testHumanishName
     */
    public function testHumanishName()
    {
        $this->initRepository('test-dir');
        $this->assertEquals('test-dir', $this->getRepository()->getHumanishName());
    }

    /**
     * testCreateFromRemote
     *
     * @return null
     */
    public function testCreateFromRemote()
    {
        $this->initRepository(null, 0);
        $remote = $this->getRepository(0);
        $remote->init();
        $this->addFile('test', null, null, $remote);
        $remote->commit('test', true);
        $remote->createBranch('develop');

        $repo = Repository::createFromRemote($remote->getPath());
        $this->assertInstanceOf('GitElephant\Repository', $repo);
        $this->assertGreaterThanOrEqual(2, $repo->getBranches());
        $branches = $repo->getBranches();
        $branchesName = array_map(
            function (Branch $b) {
                return $b->getName();
            },
            $branches
        );
        $this->assertContains('master', $branchesName);
        $this->assertContains('develop', $branchesName);
    }

    /**
     * testAddRemote
     */
    public function testRemote()
    {
        $this->initRepository(null, 0);
        $remote = $this->getRepository(0);
        $remote->init(true);
        $this->initRepository();
        $this->repository->init();
        $this->repository->addRemote('github', $remote->getPath());
        $this->assertInstanceOf('GitElephant\Objects\Remote', $this->repository->getRemote('github'));
        $this->repository->addRemote('github2', $remote->getPath());
        $this->assertCount(2, $this->repository->getRemotes());
    }

    /**
     * testFetch, git branch -a should find the branch
     */
    public function testFetch()
    {
        $this->initRepository(null, 0);
        $this->initRepository(null, 1);
        $r1 = $this->getRepository(0);
        $r1->init();
        $this->addFile('test1', null, null, $r1);
        $r1->commit('test commit', true);
        $r2 = $this->getRepository(1);
        $r2->init();
        $r2->addRemote('origin', $r1->getPath());
        $this->assertEmpty($r2->getBranches(true, true));
        $r2->fetch();
        $this->assertNotEmpty($r2->getBranches(true, true));
    }

    /**
     * test pull
     */
    public function testPull()
    {
        $this->initRepository(null, 0);
        $this->initRepository(null, 1);
        $r1 = $this->getRepository(0);
        $r1->init();
        $this->addFile('test1', null, null, $r1);
        $r1->commit('test commit', true);
        $r2 = $this->getRepository(1);
        $r2->init();
        $r2->addRemote('origin', $r1->getPath());
        $r2->pull('origin', 'master');
        $this->assertEquals('test commit', $r2->getLog()->last()->getMessage());
        $this->assertEquals($r1->getMainBranch()->getSha(), $r2->getLog()->last()->getSha());
    }

    /**
     * test pull
     */
    public function testPush()
    {
        $this->initRepository(null, 0);
        $this->initRepository(null, 1);
        $this->initRepository(null, 2);
        // commit on r1
        $r1 = $this->getRepository(0);
        $r1->init();
        $this->addFile('test1', null, null, $r1);
        $r1->commit('test commit', true);
        // push from r1 to r2
        $r2 = $this->getRepository(1);
        $r2->init(true);
        $r1->addRemote('origin', $r2->getPath());
        $r1->push('origin', 'master');
        // pull from r2 to r3 should get the same result
        $r3 = $this->getRepository(2);
        $r3->init();
        $r3->addRemote('origin', $r2->getPath());
        $r3->pull('origin', 'master');

        $this->assertEquals('test commit', $r3->getLog()->last()->getMessage());
        $this->assertEquals($r1->getMainBranch()->getSha(), $r3->getLog()->last()->getSha());
    }
}
