<?php

declare(strict_types=1);

/**
 * @author Matteo Giachino <matteog@gmail.com>
 */
namespace GitElephant\Objects;

use GitElephant\TestCase;

final class NodeObjectTest extends TestCase
{
    public function setUp(): void
    {
        $this->initRepository();
        $this->getRepository()->init(false, 'master');
        $this->addFile('test-file');
        $this->getRepository()->commit('first commit', true);
    }

    public function testGetLastCommitFromTree(): void
    {
        $tree = $this->getRepository()->getTree('master');
        $testFile = $tree[0];
        $this->assertInstanceOf(\GitElephant\Objects\Commit::class, $testFile->getLastCommit());
        $this->assertEquals('first commit', $testFile->getLastCommit()->getMessage());
    }

    public function testGetLastCommitFromBranch(): void
    {
        $this->getRepository()->createBranch('test');
        $this->getRepository()->checkout('test');
        $this->addFile('test-in-test-branch');
        $this->getRepository()->commit('test branch commit', true);
        $tree = $this->getRepository()->getTree('test', 'test-in-test-branch');
        $testFile = $tree->getBlob();
        $this->assertInstanceOf(\GitElephant\Objects\NodeObject::class, $testFile);
        $this->assertInstanceOf(\GitElephant\Objects\Commit::class, $testFile->getLastCommit());
        $this->assertSame('test branch commit', $testFile->getLastCommit()->getMessage()->getFullMessage());
    }

    public function testGetLastCommitFromTag(): void
    {
        $this->getRepository()->createTag('test-tag');
        $tag = $this->getRepository()->getTag('test-tag');
        $this->assertInstanceOf(\GitElephant\Objects\Tag::class, $tag);
        $this->assertInstanceOf(\GitElephant\Objects\Commit::class, $tag->getLastCommit());
        $this->assertInstanceOf(\GitElephant\Objects\Tag::class, $tag);
        $this->assertEquals('first commit', $tag->getLastCommit()->getMessage());
        $this->addFile('file2');
        $this->getRepository()->commit('tag 2 commit', true);
        $this->getRepository()->createTag('test-tag-2');
        $tag = $this->getRepository()->getTag('test-tag-2');
        $this->assertInstanceOf(\GitElephant\Objects\Tag::class, $tag);
        $this->assertInstanceOf(\GitElephant\Objects\Commit::class, $tag->getLastCommit());
        $this->assertInstanceOf(\GitElephant\Objects\Tag::class, $tag);
        $this->assertEquals('tag 2 commit', $tag->getLastCommit()->getMessage());
    }

    public function testRevParse(): void
    {
        $master = $this->getRepository()->getBranch('master');
        $this->assertInstanceOf(\GitElephant\Objects\Branch::class, $master);

        $revParse = $master->revParse();
        $this->assertEquals($master->getSha(), $revParse[0]);
    }

    /**
     * test repository getter and setter
     */
    public function testGetSetRepository(): void
    {
        $this->initRepository('object1', 1);
        $repo1 = $this->getRepository(1);

        $this->initRepository('object2', 2);
        $repo2 = $this->getRepository(2);

        $object = new NodeObject($repo1, 'permissions', 'type', 'sha', 'size', 'name', 'path'); // dummy params
        $this->assertSame($repo1, $object->getRepository());
        $object->setRepository($repo2);
        $this->assertSame($repo2, $object->getRepository());
    }
}
