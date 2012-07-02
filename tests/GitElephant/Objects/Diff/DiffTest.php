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

namespace GitElephant\Objects\Diff;

use GitElephant\TestCase;
use GitElephant\Objects\Diff\Diff,
    GitElephant\Objects\Diff\DiffObject,
    GitElephant\Objects\Diff\DiffChunk,
    GitElephant\Objects\Diff\DiffChunkLine,
    GitElephant\Objects\Diff\DiffChunkLineAdded,
    GitElephant\Objects\Diff\DiffChunkLineDeleted,
    GitElephant\Objects\Diff\DiffChunkLineUnchanged,
    GitElephant\Objects\Commit;

use GitElephant\Command\MainCommand,
    GitElephant\Command\DiffCommand,
    GitElephant\Command\ShowCommand;

/**
 * DiffTest
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class DiffTest extends TestCase
{
    public function setUp()
    {
        $this->initRepository();
    }

    public function testDiff()
    {
        $mainCommand = new MainCommand();
        $diffCommand = new DiffCommand();

        $this->getCaller()->execute($mainCommand->init());
        $this->addFile('foo', null, "content line 1\ncontent line 2\ncontent line 3");
        $this->getCaller()->execute($mainCommand->add());
        $this->getCaller()->execute($mainCommand->commit('first commit'));
        $this->addFile('foo', null, "content line 1\ncontent line 2 changed");
        $this->getCaller()->execute($mainCommand->add());
        $this->getCaller()->execute($mainCommand->commit('first commit'));
        $commit = $this->getRepository()->getCommit();

        $command = $diffCommand->diff($commit);
        $diff = new Diff($this->getCaller()->execute($command)->getOutputLines());

        $this->assertInstanceOf('\GitElephant\Objects\Diff\Diff', $diff);
        $this->assertArrayInterfaces($diff);
        $this->assertCount(1, $diff);
        $object = $diff[0];
        $this->assertInstanceOf('\GitElephant\Objects\Diff\DiffObject', $object);
        $this->assertArrayInterfaces($object);
        $this->assertCount(1, $object);
        $chunk = $object[0];
        $this->assertInstanceOf('\GitElephant\Objects\Diff\DiffChunk', $chunk);
        $this->assertArrayInterfaces($chunk);
        $this->assertCount(5, $chunk);
        foreach($chunk as $chunkLine) {
            $this->assertInstanceOf('\GitElephant\Objects\Diff\DiffChunkLine', $chunkLine);
        }
    }

    private function assertArrayInterfaces($obj)
    {
        $this->assertInstanceOf('\Iterator', $obj);
        $this->assertInstanceOf('\Countable', $obj);
        $this->assertInstanceOf('\ArrayAccess', $obj);
    }
}
