<?php
/**
 * @author Matteo Giachino <matteog@gmail.com>
 */

namespace GitElephant\Objects;

use GitElephant\TestCase;

class ObjectTest extends TestCase
{
    public function setUp()
    {
        $this->initRepository();
        $this->getRepository()->init();
        $this->addFile('test-file');
        $this->getRepository()->commit('first commit', true);
    }

    public function testGetLastCommitWithOneCommit()
    {
        $tree = $this->getRepository()->getTree('master');
        $testFile = $tree[0];
        $this->assertInstanceOf('GitElephant\Objects\Commit', $testFile->getLastCommit());
        $this->assertEquals('first commit', $testFile->getLastCommit()->getMessage());
    }

    public function testGetLastCommitFromAnotherBranch()
    {
        Branch::checkout($this->getRepository(), 'test', true);
        $this->getRepository()->checkout('test');
        $this->addFile('test-in-test-branch');
        $this->getRepository()->commit('test branch commit', true);
        $tree = $this->getRepository()->getTree('test');
        $testFile = $tree[0];
        $this->assertInstanceOf('GitElephant\Objects\Commit', $testFile->getLastCommit());
        $this->assertEquals('test branch commit', $testFile->getLastCommit()->getMessage());
    }

    public function testGetLastCommitFromTag()
    {
        $this->getRepository()->createTag('test-tag');
        $tag = $this->getRepository()->getTag('test-tag');
        $this->assertInstanceOf('GitElephant\Objects\Commit', $tag->getLastCommit());
        $this->assertEquals('test branch commit', $tag->getLastCommit()->getMessage());
    }
}
