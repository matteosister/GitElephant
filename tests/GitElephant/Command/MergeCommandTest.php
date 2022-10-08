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
    public function setUp(): void
    {
        $this->initRepository();
        $this->getRepository()->init(false, 'master');
        $this->addFile('test');
        $this->getRepository()->commit('test', true);
        $this->getRepository()->createBranch('test', 'master');
    }

    /**
     * testMerge
     */
    public function testMerge(): void
    {
        $mc = MergeCommand::getInstance();
        $branch = $this->getRepository()->getBranch('test');
        $this->assertEquals("merge 'refs/heads/test'", $mc->merge($branch));
        $this->assertEquals("merge '-m' 'test msg' 'refs/heads/test'", $mc->merge($branch, "test msg"));
        $this->assertEquals(
            "merge '--ff-only' '-m' 'test msg' 'refs/heads/test'",
            $mc->merge($branch, "test msg", ['--ff-only'])
        );
        $this->assertEquals(
            "merge '--no-ff' '-m' 'test msg' 'refs/heads/test'",
            $mc->merge($branch, "test msg", ['--no-ff'])
        );
    }

    /**
     * MergeCommand should throw an exception when both --ff-only and --no-ff flags were set.
     */
    public function testExceptionWhenCallingMergeWithConflictingFfArguments(): void
    {
        $branch = $this->getRepository()->getBranch('test');
        $this->expectException(\Symfony\Component\Process\Exception\InvalidArgumentException::class);
        MergeCommand::getInstance()->merge($branch, "test msg", ['--ff-only', '--no-ff']);
    }
}
