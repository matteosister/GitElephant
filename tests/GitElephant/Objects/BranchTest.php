<?php
/**
 * User: matteo
 * Date: 05/01/13
 * Time: 0.18
 * 
 * Just for fun...
 */

namespace GitElephant\Objects;

use \GitElephant\TestCase;
use \GitElephant\Objects\Branch;

/**
 * Branch tests
 */
class BranchTest extends TestCase
{
    /**
     * testGetMatches
     */
    public function testGetMatches()
    {
        $matches = Branch::getMatches('* develop 45eac8c31adfbbf633824cee6ce8cc5040b33513 test message');
        $this->assertEquals('develop', $matches[1]);
        $this->assertEquals('45eac8c31adfbbf633824cee6ce8cc5040b33513', $matches[2]);
        $this->assertEquals('test message', $matches[3]);

        $matches = Branch::getMatches('  develop 45eac8c31adfbbf633824cee6ce8cc5040b33513 test message');
        $this->assertEquals('develop', $matches[1]);
        $this->assertEquals('45eac8c31adfbbf633824cee6ce8cc5040b33513', $matches[2]);
        $this->assertEquals('test message', $matches[3]);

        $matches = Branch::getMatches('  test/branch 45eac8c31adfbbf633824cee6ce8cc5040b33513 test "message" with?');
        $this->assertEquals('test/branch', $matches[1]);
        $this->assertEquals('45eac8c31adfbbf633824cee6ce8cc5040b33513', $matches[2]);
        $this->assertEquals('test "message" with?', $matches[3]);

        $matches = Branch::getMatches("* (detached from 7a02066) 7a020660489a31ed9d0d6a42d5a0f0379334ba82 Merge branch 'PERFORM' into 'master'");
        $this->assertEquals('detached', $matches[1]);
        $this->assertEquals('7a020660489a31ed9d0d6a42d5a0f0379334ba82', $matches[2]);
        $this->assertEquals("Merge branch 'PERFORM' into 'master'", $matches[3]);
    }

    /**
     * testGetMatchesErrors
     */
    public function testGetMatchesShortShaError()
    {
        // short sha
        $this->expectException(\InvalidArgumentException::class);
        $matches = Branch::getMatches('* develop 45eac8c31adfbbf633824cee6ce8cc5040b3351 test message');
    }

    /**
     * testGetMatchesErrors
     *
     */
    public function testGetMatchesNoSpaceError()
    {
        $this->expectException(\InvalidArgumentException::class);
        $matches = Branch::getMatches('* develop 45eac8c31adfbbf633824cee6ce8cc5040b33511test message');
    }

    /**
     * test constructor
     */
    public function testConstructor()
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('test commit', true);
        $b = new Branch($this->getRepository(), 'master');
        $this->assertEquals('master', $b->getName());
        $this->assertEquals('test commit', $b->getComment());
        $this->assertTrue($b->getCurrent());
        $this->getRepository()->createBranch('develop');
        $b = new Branch($this->getRepository(), 'develop');
        $this->assertEquals('develop', $b->getName());
        $this->assertEquals('test commit', $b->getComment());
        $this->assertFalse($b->getCurrent());
        $this->expectException('GitElephant\Exception\InvalidBranchNameException');
        $this->fail(Branch::checkout($this->getRepository(), 'non-existent'));
    }

    /**
     * testBranchCreate
     */
    public function testBranchCreate()
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('test', true);
        Branch::create($this->getRepository(), 'test-branch');
        $this->assertCount(2, $this->getRepository()->getBranches());
        $this->assertContains('test-branch', $this->getRepository()->getBranches(true));
    }

    /**
     * __toString
     */
    public function testToString()
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('test commit', true);
        $b = Branch::checkout($this->getRepository(), 'master');
        $this->assertEquals($this->getRepository()->getLog()->last()->getSha(), $b->__toString());
    }

    /**
     * testCreate
     */
    public function testCreate()
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->repository->commit('test', true);
        $this->assertCount(1, $this->repository->getBranches(true));
        Branch::create($this->repository, 'test-branch');
        $this->assertCount(2, $this->repository->getBranches(true));
        Branch::create($this->repository, 'test-branch2', 'test-branch');
        $this->assertCount(3, $this->repository->getBranches(true));
    }
}
