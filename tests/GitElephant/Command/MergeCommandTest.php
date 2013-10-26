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
    }
}