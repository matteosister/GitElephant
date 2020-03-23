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

        $this->assertInstanceOf('\GitElephant\Objects\Diff\DiffChunkLineUnchanged', $chunk[0]);
        $this->assertInstanceOf('\GitElephant\Objects\Diff\DiffChunkLineChanged', $chunk[1]);
        $this->assertInstanceOf('\GitElephant\Objects\Diff\DiffChunkLineDeleted', $chunk[2]);
        $this->assertInstanceOf('\GitElephant\Objects\Diff\DiffChunkLineAdded', $chunk[3]);
        $this->assertInstanceOf('\GitElephant\Objects\Diff\DiffChunkLineUnchanged', $chunk[4]);
        
        $this->assertEquals(1, ($chunk[0])->getOriginNumber());
        $this->assertEquals(1, ($chunk[0])->getDestNumber());
        $this->assertEquals(2, ($chunk[1])->getNumber());
        $this->assertEquals(2, ($chunk[1])->getOriginNumber());
        $this->assertEquals(3, ($chunk[4])->getDestNumber());
        $this->assertEquals(4, ($chunk[4])->getOriginNumber());
    }

    private function assertArrayInterfaces($obj): void
    {
        $this->assertInstanceOf('\Iterator', $obj);
        $this->assertInstanceOf('\Countable', $obj);
        $this->assertInstanceOf('\ArrayAccess', $obj);
    }
}
