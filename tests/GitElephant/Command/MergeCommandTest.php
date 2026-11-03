<?php

declare(strict_types=1);

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
final class MergeCommandTest extends TestCase
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
        $this->assertInstanceOf(\GitElephant\Objects\Branch::class, $branch);
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

        $this->getRepository()->createTag('v1.0');
        $tag = $this->getRepository()->getTag('v1.0');
        $this->assertInstanceOf(\GitElephant\Objects\Tag::class, $tag);
        $this->assertEquals("merge 'refs/tags/v1.0'", $mc->merge($tag));
        $this->assertEquals("merge 'refs/tags/v1.0'", $mc->merge('refs/tags/v1.0'));

        $commit = $this->getRepository()->getCommit();
        $this->assertEquals(sprintf("merge '%s'", $commit->getSha()), $mc->merge($commit));
    }

    /**
     * MergeCommand should throw an exception when both --ff-only and --no-ff flags were set.
     */
    public function testExceptionWhenCallingMergeWithConflictingFfArguments(): void
    {
        $branch = $this->getRepository()->getBranch('test');
        $this->expectException(\Symfony\Component\Process\Exception\InvalidArgumentException::class);
        $this->assertInstanceOf(\GitElephant\Objects\Branch::class, $branch);
        MergeCommand::getInstance()->merge($branch, "test msg", ['--ff-only', '--no-ff']);
    }
}
