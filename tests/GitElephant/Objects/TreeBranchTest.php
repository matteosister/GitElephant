<?php
/**
 * User: matteo
 * Date: 05/01/13
 * Time: 0.18
 * 
 * Just for fun...
 */

namespace GitElephant\Objects;

use GitElephant\TestCase;
use GitElephant\Objects\TreeBranch;

/**
 * TreeBranch tests
 */
class TreeBranchTest extends TestCase
{
    /**
     * testGetMatches
     */
    public function testGetMatches()
    {
        $matches = TreeBranch::getMatches('* develop 45eac8c31adfbbf633824cee6ce8cc5040b33513 test message');
        $this->assertEquals('develop', $matches[1]);
        $this->assertEquals('45eac8c31adfbbf633824cee6ce8cc5040b33513', $matches[2]);
        $this->assertEquals('test message', $matches[3]);
        $matches = TreeBranch::getMatches('  develop 45eac8c31adfbbf633824cee6ce8cc5040b33513 test message');
        $this->assertEquals('develop', $matches[1]);
        $this->assertEquals('45eac8c31adfbbf633824cee6ce8cc5040b33513', $matches[2]);
        $this->assertEquals('test message', $matches[3]);
        $matches = TreeBranch::getMatches('  test/branch 45eac8c31adfbbf633824cee6ce8cc5040b33513 test "message" with?');
        $this->assertEquals('test/branch', $matches[1]);
        $this->assertEquals('45eac8c31adfbbf633824cee6ce8cc5040b33513', $matches[2]);
        $this->assertEquals('test "message" with?', $matches[3]);
    }

    /**
     * testGetMatchesErrors
     *
     * @expectedException \InvalidArgumentException
     */
    public function testGetMatchesShortShaError()
    {
        // short sha
        $matches = TreeBranch::getMatches('* develop 45eac8c31adfbbf633824cee6ce8cc5040b3351 test message');
    }

    /**
     * testGetMatchesErrors
     *
     * @expectedException \InvalidArgumentException
     */
    public function testGetMatchesNoSpaceError()
    {
        $matches = TreeBranch::getMatches('* develop 45eac8c31adfbbf633824cee6ce8cc5040b33511test message');
    }

    /**
     * test constructor
     */
    public function testConstructor()
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('test commit', true);
        $b = new TreeBranch($this->getRepository(), 'master');
        $this->assertEquals('master', $b->getName());
        $this->assertEquals('test commit', $b->getComment());
        $this->assertTrue($b->getCurrent());
        $this->getRepository()->createBranch('develop');
        $b = new TreeBranch($this->getRepository(), 'develop');
        $this->assertEquals('develop', $b->getName());
        $this->assertEquals('test commit', $b->getComment());
        $this->assertFalse($b->getCurrent());
    }

    /**
     * testGetUpstream
     */
    public function testGetUpstream()
    {
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('test commit', true);
        $branch = new TreeBranch($this->getRepository(), 'master');
        $mockCaller = $this->getMockCaller(null, array('* master a664686e47fb8fb2ffa3e512bdbd380face4e577 [origin/master: dietro 122] Merge pull request #8732 from amatsuda/readme_call_yield'));
        $this->getRepository()->setCaller($mockCaller);
        $this->assertEquals('origin/master', $branch->getUpstream());
        $mockCaller = $this->getMockCaller(null, array(' develop a664686e47fb8fb2ffa3e512bdbd380face4e577 [origin/develop] Merge pull request #8732 from amatsuda/readme_call_yield'));
        $this->getRepository()->setCaller($mockCaller);
        $this->assertEquals('origin/develop', $branch->getUpstream());
    }
}
