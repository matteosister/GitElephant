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

use \GitElephant\TestCase;
use \GitElephant\Objects\Diff\Diff;
use \GitElephant\Objects\Diff\DiffObject;
use \GitElephant\Objects\Diff\DiffChunk;
use \GitElephant\Objects\Diff\DiffChunkLine;
use \GitElephant\Objects\Diff\DiffChunkLineAdded;
use \GitElephant\Objects\Diff\DiffChunkLineDeleted;
use \GitElephant\Objects\Diff\DiffChunkLineUnchanged;
use \GitElephant\Objects\Commit;

use \GitElephant\Command\MainCommand;
use \GitElephant\Command\DiffCommand;
use \GitElephant\Command\ShowCommand;

/**
 * DiffTest
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class DiffTest extends TestCase
{
    public function setUp(): void
    {
        $this->initRepository();
    }

    public function testDiff(): void
    {
        $mainCommand = new MainCommand();
        $diffCommand = new DiffCommand();

        $this->getRepository()->init();
        $this->addFile('foo', null, "content line 1\ncontent line 2\ncontent line 3");
        $this->getRepository()->commit('commit1', true);
        $this->addFile('foo', null, "content line 1\ncontent line 2 changed");
        $this->getRepository()->commit('commit2', true);
        $commit = $this->getRepository()->getCommit();

        $diff = Diff::create($this->getRepository(), $commit);

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
        foreach ($chunk as $chunkLine) {
            $this->assertInstanceOf('\GitElephant\Objects\Diff\DiffChunkLine', $chunkLine);
        }
    }

    private function assertArrayInterfaces($obj): void
    {
        $this->assertInstanceOf('\Iterator', $obj);
        $this->assertInstanceOf('\Countable', $obj);
        $this->assertInstanceOf('\ArrayAccess', $obj);
    }
}
