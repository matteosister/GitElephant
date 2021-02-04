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

use GitElephant\Command\ResetCommand;
use GitElephant\Objects\Branch;
use GitElephant\Objects\Log;
use GitElephant\Objects\NodeObject;
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
    public function setUp(): void
    {
        $this->initRepository();
    }

    /**
     * @covers \GitElephant\Repository::__construct
     * @covers \GitElephant\Repository::getPath
     */
    public function testConstruct(): void
    {
        $this->assertEquals($this->getRepository()->getPath(), $this->path);

        $this->expectException('GitElephant\Exception\InvalidRepositoryPathException');
        $repo = new Repository('non-existent-path');

        $repo = Repository::open($this->path);
        $this->assertInstanceOf('GitElephant\Repository', $repo);
    }

    /**
     * @covers \GitElephant\Repository::init
     */
    public function testInit(): void
    {
        $this->getRepository()->init();
        $match = false;

        // Force US/EN locale
        putenv('LANG=en_US.UTF-8');

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
    public function testName(): void
    {
        $this->getRepository()->setName('test-repo');
        $this->assertEquals('test-repo', $this->getRepository()->getName());
    }

    /**
     * @covers \GitElephant\Repository::stage
     */
    public function testStage(): void
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
     * @covers \GitElephant\Repository::unstage
     */
    public function testUnstage(): void
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('first commit', true);
        $this->addFile('test2');
        $this->assertCount(1, $this->getRepository()->getStatus()->untracked());
        $this->assertCount(0, $this->getRepository()->getStatus()->added());
        $this->getRepository()->stage('test2');
        $this->assertCount(0, $this->getRepository()->getStatus()->untracked());
        $this->assertCount(1, $this->getRepository()->getStatus()->added());
        $this->getRepository()->unstage('test2');
        $this->assertCount(1, $this->getRepository()->getStatus()->untracked());
        $this->assertCount(0, $this->getRepository()->getStatus()->added());
    }

    /**
     * @covers \GitElephant\Repository::commit
     * @covers \GitElephant\Repository::getStatusOutput
     */
    public function testCommit(): void
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

        // Commit something with a custom date.
        $this->addFile('test3');
        $this->getRepository()->commit('commit 3', true, 'develop', null, false, new \DateTimeImmutable('1981-09-24'));
        $log = $this->getRepository()->getLog('develop', null, 1)->current();
        $this->assertEquals('1981-09-24', $log->getDatetimeAuthor()->format('Y-m-d'));
    }

    /**
     * @covers \GitElephant\Repository::getStatusOutput
     */
    public function testGetStatus(): void
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('test commit', true);
        $output = $this->getRepository()->getStatusOutput();
        $this->assertStringEndsWith('master', $output[0]);
        $this->addFile('file2');
        $output = $this->getRepository()->getStatusOutput();
        $this->assertContains('file2', $output);
    }

    /**
     * @covers \GitElephant\Repository::createBranch
     */
    public function testCreateBranch(): void
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('foo', true);
        $this->getRepository()->createBranch('test-branch');
        $this->assertEquals(2, count($this->getRepository()->getBranches()));
    }

    /**
     * @covers \GitElephant\Repository::deleteBranch
     */
    public function testDeleteBranch(): void
    {
        $this->getRepository()->init();
        $this->addFile('test-file');
        $this->getRepository()->commit('test', true);
        $this->getRepository()->createBranch('branch2');
        $this->assertEquals(2, count($this->getRepository()->getBranches(true)));
        $this->getRepository()->deleteBranch('branch2');
        $this->assertEquals(1, count($this->getRepository()->getBranches(true)));
        $this->addFile('test-file2');
        $this->getRepository()->commit('test2', true);
        $this->getRepository()->createBranch('branch3');
        $this->assertEquals(2, count($this->getRepository()->getBranches(true)));
        $this->getRepository()->deleteBranch('branch3', true);
        $this->assertEquals(1, count($this->getRepository()->getBranches(true)));
    }

    /**
     * @covers \GitElephant\Repository::getBranches
     */
    public function testGetBranches(): void
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
        /** @var Branch $branch */
        $branch = $branches[0];
        $this->assertEquals('master', $branch->getName());
        $this->getRepository()->deleteBranch('test-branch');
        $this->assertCount(1, $this->getRepository()->getBranches(), 'one branch expected');
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
        $this->assertEquals(['master'], $this->getRepository()->getBranches(true));
        $this->getRepository()->createBranch('develop');
        $this->assertContains('master', $this->getRepository()->getBranches(true));
        $this->assertContains('develop', $this->getRepository()->getBranches(true));
    }

    /**
     * @covers \GitElephant\Repository::getMainBranch
     */
    public function testGetMainBranch(): void
    {
        $this->getRepository()->init();
        $this->addFile('test-file');
        $this->getRepository()->commit('test', true);
        $this->assertEquals('master', $this->getRepository()->getMainBranch()->getName());
    }

    /**
     * @covers \GitElephant\Repository::getBranch
     */
    public function testGetBranch(): void
    {
        $this->getRepository()->init();
        $this->addFile('test-file');
        $this->getRepository()->commit('test', true);
        $this->assertInstanceOf('GitElephant\Objects\Branch', $this->getRepository()->getBranch('master'));
        $this->assertNull($this->getRepository()->getBranch('a-branch-that-do-not-exists'));
    }

    /**
     * @covers \GitElephant\Repository::merge
     */
    public function testMerge(): void
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

        // attempt to merge a different branch by forcing a 3-way merge and verify the merge commit message
        $this->getRepository()->createBranch('branch3');
        $this->getRepository()->checkout('branch3');
        $this->addFile('file3');
        $this->getRepository()->commit('test3', true);
        $this->assertEquals(3, count($this->getRepository()->getTree()));
        $this->getRepository()->checkout('master');
        $this->assertEquals(2, count($this->getRepository()->getTree()));
        $this->getRepository()->merge($this->getRepository()->getBranch('branch3'), 'test msg', 'no-ff');
        $this->assertEquals(3, count($this->getRepository()->getTree()));
        $this->assertEquals('test msg', $this->getRepository()->getCommit()->getMessage()->getFullMessage());

        // attempt a fast forward merge where a 3-way is necessary and trap the resulting exception
        $this->getRepository()->checkout('branch2');
        $this->addFile('file4');
        $this->getRepository()->commit('test4', true);
        $this->assertEquals(3, count($this->getRepository()->getTree()));
        $this->getRepository()->checkout('master');
        $this->assertEquals(3, count($this->getRepository()->getTree()));
        try {
            $this->getRepository()->merge($this->getRepository()->getBranch('branch2'), '', 'ff-only');
        } catch (\RuntimeException $e) {
            return;
        }
        $this->fail("Merge should have produced a runtime exception.");
    }

    /**
     * @covers \GitElephant\Repository::getTags
     * @covers \GitElephant\Repository::getTag
     * @covers \GitElephant\Repository::createTag
     * @covers \GitElephant\Repository::deleteTag
     */
    public function testTags(): void
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
    public function testGetLastTag(): void
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
     * @covers \GitElephant\Repository::getCommit
     */
    public function testGetCommit(): void
    {
        $this->getRepository()->init();
        $this->addFile('test-file');
        $this->getRepository()->commit('test', true);
        $this->assertInstanceOf('GitElephant\Objects\Commit', $this->getRepository()->getCommit());
    }

    public function testGetBranchOrTag(): void
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
     * @covers \GitElephant\Repository::getObjectLog
     */
    public function testGetObjectLog(): void
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
        /** @var NodeObject $obj */
        $obj = $tree[0];

        $log = $this->getRepository()->getObjectLog($obj);
        $this->assertInstanceOf(Log::class, $log);
        $this->assertEquals(1, $log->count());

        $log = $this->getRepository()->getObjectLog($obj, null, 10);
        $this->assertEquals(5, $log->count());

        $this->assertEquals('added E.txt', $log->first()->getMessage()->toString());
        $this->assertEquals('added A.txt', $log->last()->getMessage()->toString());
    }

    /**
     * Test logs on different tree objects
     *
     * @covers \GitElephant\Repository::getObjectLog
     */
    public function testGetObjectLogFolders(): void
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

        /** @var NodeObject $treeObj */
        foreach ($tree as $treeObj) {
            $name = $treeObj->getName();
            $log = $repo->getObjectLog($treeObj, null, 10);

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
     * @covers \GitElephant\Repository::getObjectLog
     */
    public function testGetObjectLogBranches(): void
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
        $log = $repo->getObjectLog($dir, null, 10);

        $this->assertEquals(2, $log->count());
        $this->assertEquals('A/A2', $log->first()->getMessage()->toString());

        // test branch
        $repo->checkout('test-branch');
        $tree = $repo->getTree();
        $dir = $tree[0];
        $log = $repo->getObjectLog($dir, null, 10);

        $this->assertEquals(3, $log->count());
        $this->assertEquals('A/A3', $log->first()->getMessage()->toString());
    }

    /**
     * @covers \GitElephant\Repository::getLog
     */
    public function testGetLog(): void
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
     * @covers \GitElephant\Repository::getLog
     */
    public function testGetLogForBranch(): void
    {
        $this->getRepository()->init();
        $this->addFile('test file 0');
        $this->getRepository()->commit('first commit', true);
        $this->getRepository()->checkout('test-branch', true);

        for ($i = 1; $i <= 2; $i++) {
            $this->addFile('test file ' . $i);
            $this->getRepository()->commit('test commit ' . $i, true);
        }

        $log = $this->getRepository()->getLog(['test-branch', '^master']);
        $this->assertInstanceOf('GitElephant\Objects\Log', $this->getRepository()->getLog());
        $this->assertEquals(2, $log->count());
    }

    /**
     * @covers \GitElephant\Repository::checkout
     */
    public function testCheckout(): void
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
     * @covers \GitElephant\Repository::checkout
     */
    public function testCheckoutTag(): void
    {
        $this->getRepository()->init();
        $this->addFile('test-file');
        $this->getRepository()->commit('test', true);
        $this->getRepository()->createTag('v0.0.1');
        $this->addFile('test-file2');
        $this->getRepository()->commit('test2', true);
        $tag = $this->getRepository()->getTag('v0.0.1');
        $this->assertInstanceOf('GitElephant\Objects\Tag', $tag);
        $lastCommit = $this->getRepository()->getCommit();
        $this->assertStringNotContainsString('detached', implode(' ', $this->getRepository()->getStatusOutput()));
        $this->getRepository()->checkout($tag);
        $newCommit = $this->getRepository()->getCommit();
        $this->assertNotEquals($newCommit->getSha(), $lastCommit->getSha());
        $this->assertStringContainsString('detached', implode(' ', $this->getRepository()->getStatusOutput()));
    }

    /**
     * @covers \GitElephant\Repository::getTree
     * @covers \GitElephant\Objects\Tree
     */
    public function testGetTree(): void
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
            'GitElephant\Objects\NodeObject',
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
            'GitElephant\Objects\NodeObject',
            $secondNode,
            'array access on tree should give always a node type'
        );
        $this->assertEquals(
            NodeObject::TYPE_BLOB,
            $secondNode->getType(),
            'second node should be of type tree'
        );
        $subtree = $this->getRepository()->getTree('master', 'test-folder');
        $subnode = $subtree[0];
        $this->assertInstanceOf(
            'GitElephant\Objects\NodeObject',
            $subnode,
            'array access on tree should give always a node type'
        );
        $this->assertEquals(
            NodeObject::TYPE_BLOB,
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
     * @covers \GitElephant\Repository::getDiff
     */
    public function testGetDiff(): void
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
    public function testCloneFrom(): void
    {
        $this->initRepository(null, 0);
        $this->initRepository(null, 1);
        $remote = $this->getRepository(0);
        $remote->init();
        $this->addFile('test', null, null, $remote);
        $remote->commit('test', true);
        $local = $this->getRepository(1);
        $local->cloneFrom($remote->getPath(), '.', 'master', 1, false);
        $commit = $local->getCommit();
        $this->assertEquals($remote->getCommit()->getSha(), $commit->getSha());
        $this->assertEquals($remote->getCommit()->getMessage(), $commit->getMessage());
    }

    /**
     * testOutputContent
     */
    public function testOutputContent(): void
    {
        $this->initRepository();
        $this->getRepository()->init();
        $this->addFile('file1', null, 'file content');
        $this->getRepository()->commit('first commit', true);
        $branch = $this->getRepository()->getBranch('master');
        $tree = $this->getRepository()->getTree($branch, 'file1');
        $treeObject = $tree->getBlob();
        $this->assertEquals(['file content'], $this->getRepository()->outputContent($treeObject, $branch));
    }

    /**
     * testMove
     */
    public function testMove(): void
    {
        $this->getRepository()->init();
        $this->addFile('foo');
        $this->getRepository()->commit('commit 1', true);
        $this->getRepository()->move('foo', 'bar');
        $status = $this->getRepository()->getStatusOutput();
        $this->myAssertMatchesRegularExpression('/(.*):    foo -> bar/', implode("\n", $status));
    }

    /**
     * testRemove
     */
    public function testRemove(): void
    {
        $this->getRepository()->init();
        $this->addFile('foo');
        $this->getRepository()->commit('commit 1', true);
        $this->getRepository()->remove('foo');
        $status = $this->getRepository()->getStatusOutput();

        $this->myAssertMatchesRegularExpression('/(.*):    foo/', implode("\n", $status));
    }

    /**
     * testCountCommits
     */
    public function testCountCommits(): void
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
    public function testHumanishName(): void
    {
        $this->initRepository('test-dir');
        $this->assertEquals('test-dir', $this->getRepository()->getHumanishName());
    }

    /**
     * testCreateFromRemote
     */
    public function testCreateFromRemote(): void
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
    public function testRemote(): void
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
    public function testFetch(): void
    {
        $this->initRepository(null, 0);
        $this->initRepository(null, 1);
        $r1 = $this->getRepository(0);
        $r1->init();
        $this->addFile('test1', null, null, $r1);
        $r1->commit('test commit', true);
        $r1->createBranch('tag-test');
        $this->addFile('test2', null, null, $r1);
        $r1->commit('another test commit', true);
        $r1->createTag('test-tag');
        $r2 = $this->getRepository(1);
        $r2->init();
        $r2->addRemote('origin', $r1->getPath());
        $this->assertEmpty($r2->getBranches(true, true));
        $r2->fetch();
        $this->assertNotEmpty($r2->getBranches(true, true));
        $r2->fetch(null, null, true);
        $this->assertNotNull($r2->getTag('test-tag'));
    }

    /**
     * test pull
     */
    public function testPull(): void
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
    public function testPush(): void
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

    public function testRevParse(): void
    {
        $this->initRepository(null, 0);
        $r = $this->getRepository(0);
        $r->init();
        $this->addFile('test1', null, null, $r);
        $r->commit('test commit', true);
        $master = $r->getBranch('master');
        $revParse = $r->revParse($master, []);
        $this->assertEquals($master->getSha(), $revParse[0]);
    }

    public function testIsBare(): void
    {
        $this->initRepository(null, 0);
        $r = $this->getRepository(0);
        $r->init();

        $this->assertEquals(false, $r->isBare());

        $this->initRepository(null, 1);
        $r = $this->getRepository(1);
        $r->init(true);

        $this->assertEquals(true, $r->isBare());
    }

    /**
     * test add, remove and get global configs
     *
     * @covers \GitElephant\Repository::addGlobalConfig
     * @covers \GitElephant\Repository::getGlobalConfigs
     * @covers \GitElephant\Repository::removeGlobalConfig
     */
    public function testGlobalConfigs(): void
    {
        $repo = $this->getRepository();

        $configs = [
            'test1' => true,
            'test2' => 1,
            'test3' => 'value',
        ];
        $this->assertEmpty($repo->getGlobalConfigs());

        foreach ($configs as $configName => $configValue) {
            $repo->addGlobalConfig($configName, $configValue);
        }
        $this->assertSame($configs, $repo->getGlobalConfigs());

        foreach (array_keys($configs) as $configName) {
            $repo->removeGlobalConfig($configName);
        }
        $this->assertEmpty($repo->getGlobalConfigs());
    }

    /**
     * test reset
     */
    public function testResetHard(): void
    {
        $this->initRepository();
        $repo = $this->getRepository();
        $repo->init();
        $this->addFile('file1');
        $repo->stage();
        $repo->commit('message1');
        $headCommit = $repo->getCommit();
        $this->addFile('file2');
        $repo->stage();
        $repo->commit('message2');

        $this->assertEquals(2, $repo->countCommits());
        $repo->reset($headCommit, [ResetCommand::OPTION_HARD]);
        $this->assertEquals(1, $repo->countCommits());
        $this->assertEmpty($repo->getIndexStatus()->added());
    }

    /**
     * test reset
     */
    public function testResetSoft(): void
    {
        $this->initRepository();
        $repo = $this->getRepository();
        $repo->init();
        $this->addFile('file1');
        $repo->stage();
        $repo->commit('message1');
        $headCommit = $repo->getCommit();
        $this->addFile('file2');
        $repo->stage();
        $repo->commit('message2');

        $this->assertEquals(2, $repo->countCommits());
        $repo->reset($headCommit, [ResetCommand::OPTION_SOFT]);
        $this->assertEquals(1, $repo->countCommits());
        $this->assertNotEmpty($repo->getIndexStatus()->added());
    }

    /**
     * test add, remove and get global options
     *
     * @covers \GitElephant\Repository::addGlobalOption
     * @covers \GitElephant\Repository::getGlobalOptions
     * @covers \GitElephant\Repository::removeGlobalOption
     */
    public function testGlobalOptions(): void
    {
        $repo = $this->getRepository();

        $options = [
            'test1' => true,
            'test2' => 1,
            'test3' => 'value',
        ];
        $this->assertEmpty($repo->getGlobalOptions());

        foreach ($options as $configName => $configValue) {
            $repo->addGlobalOption($configName, $configValue);
        }
        $this->assertSame($options, $repo->getGlobalOptions());

        foreach (array_keys($options) as $configName) {
            $repo->removeGlobalOption($configName);
        }
        $this->assertEmpty($repo->getGlobalOptions());
    }

    /**
     * test add, remove and get global command arguments
     *
     * @covers \GitElephant\Repository::addGlobalCommandArgument
     * @covers \GitElephant\Repository::getGlobalCommandArguments
     * @covers \GitElephant\Repository::removeGlobalCommandArgument
     */
    public function testGlobalCommandArguments(): void
    {
        $repo = $this->getRepository();

        $args = [
            true,
            1,
            'value',
        ];
        $this->assertEmpty($repo->getGlobalCommandArguments());

        foreach ($args as $configValue) {
            $repo->addGlobalCommandArgument($configValue);
        }
        $this->assertSame($args, $repo->getGlobalCommandArguments());

        foreach ($args as $configValue) {
            $repo->removeGlobalCommandArgument($configValue);
        }
        $this->assertEmpty($repo->getGlobalCommandArguments());
    }

    /**
     * @covers \GitElephant\Repository::stash
     */
    public function testStashThrowsExceptionIfNoCommits(): void
    {
        $this->getRepository()->init();
        $this->addFile('test');

        $this->expectException('RuntimeException');
        $this->getRepository()->stash('My stash', true);
    }

    /**
     * @covers \GitElephant\Repository::stash
     */
    public function testStash(): void
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('Test commit', true);
        $this->addFile('test2');
        $this->getRepository()->stash('My stash', true);
        $this->assertTrue($this->getRepository()->isClean());
        $stashList = $this->getRepository()->stashList();
        $this->assertEquals(1, preg_match('%My stash%', $stashList[0]));
    }

    /**
     * @covers \GitElephant\Repository::stashList
     */
    public function testStashList(): void
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('Test commit', true);
        $this->addFile('test2');
        $this->getRepository()->stash('My stash', true);
        $this->assertCount(1, $this->getRepository()->stashList());
    }

    /**
     * @covers \GitElephant\Repository::stashShow
     */
    public function testStashShow(): void
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('Test commit', true);
        $this->addFile('test2');
        $this->getRepository()->stash('My stash', true);
        $this->assertIsString($this->getRepository()->stashShow(0));
    }

    /**
     * @covers \GitElephant\Repository::stashDrop
     */
    public function testStashDrop(): void
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('Test commit', true);
        $this->addFile('test2');
        $this->getRepository()->stash('My stash', true);
        $this->getRepository()->stashDrop(0);
        $this->assertCount(0, $this->getRepository()->stashList());
    }

    /**
     * @covers \GitElephant\Repository::stashPop
     */
    public function testStashPop(): void
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('Test commit', true);
        $this->addFile('test2');
        $this->getRepository()->stash('My stash', true);
        $this->getRepository()->stashPop(0);
        $this->assertTrue($this->getRepository()->isDirty());
        $this->assertCount(0, $this->getRepository()->stashList());
    }

    /**
     * @covers \GitElephant\Repository::stashApply
     */
    public function testStashApply(): void
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('Test commit', true);
        $this->addFile('test2');
        $this->getRepository()->stash('My stash', true);
        $this->getRepository()->stashApply(0);
        $this->assertTrue($this->getRepository()->isDirty());
        $this->assertCount(1, $this->getRepository()->stashList());
    }

    /**
     * @covers \GitElephant\Repository::stashBranch
     */
    public function testStashBranch(): void
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('Test commit', true);
        $this->addFile('test2');
        $this->getRepository()->stash('My stash', true);
        $this->getRepository()->stashBranch('testbranch', 0);
        $this->assertEquals('testbranch', $this->getRepository()->getMainBranch()->getName());
    }

    /**
     * @covers \GitElephant\Repository::stashCreate
     */
    public function testStashCreate(): void
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('Test commit', true);
        $objectName = $this->getRepository()->stashCreate();
        $this->assertIsString($objectName);
    }

    /**
     * @covers \GitElephant\Repository::stashCreate
     */
    public function testStashClear(): void
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('Test commit', true);
        $this->addFile('test2');
        $this->getRepository()->stash('My stash', true);
        $this->addFile('test3');
        $this->getRepository()->stash('My stash 2', true);
        $this->getRepository()->stashClear();
        $this->assertCount(0, $this->getRepository()->stashList());
    }
}
