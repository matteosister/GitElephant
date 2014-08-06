<?php
/**
 * User: matteo
 * Date: 28/05/13
 * Time: 18.36
 * Just for fun...
 */

namespace GitElephant\Command;

use GitElephant\TestCase;

/**
 * Class MergeCommandTest
 *
 * @package GitElephant\Command
 */
class MergeCommandTest extends TestCase
{
    /**
     * setUp
     */
    public function setUp()
    {
        $this->initRepository();
        $this->getRepository()->init();
        $this->addFile('test');
        $this->getRepository()->commit('test', true);
        $this->getRepository()->createBranch('test', 'master');
    }

    /**
     * testMerge
     */
    public function testMerge()
    {
        $branch = $this->getRepository()->getBranch('test');
        $this->assertEquals("merge 'refs/heads/test'", MergeCommand::getInstance()->merge($branch));
        $this->assertEquals("merge '-m' 'test msg' 'refs/heads/test'", MergeCommand::getInstance()->merge($branch, "test msg"));
        $this->assertEquals("merge '--ff-only' '-m' 'test msg' 'refs/heads/test'", MergeCommand::getInstance()->merge($branch, "test msg", array('--ff-only')));
        $this->assertEquals("merge '--no-ff' '-m' 'test msg' 'refs/heads/test'", MergeCommand::getInstance()->merge($branch, "test msg", array('--no-ff')));
        
        try {
            MergeCommand::getInstance()->merge($branch, "test msg", array('--ff-only', '--no-ff'));
        } catch (\Symfony\Component\Process\Exception\InvalidArgumentException $e) {
            return;
        }
        $this->fail("MergeCommand failed to throw an exception when both --ff-only and --no-ff flags were set.");
    }
}
