<?php

declare(strict_types=1);

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

final class DiffTest extends TestCase
{
    public function setUp(): void
    {
        $this->initRepository();
    }

    public function testDiff(): void
    {
        $this->getRepository()->init(false, 'master');
        $this->addFile('foo', null, "content line 1\ncontent line 2\ncontent line 3");
        $this->getRepository()->commit('commit1', true);
        $this->addFile('foo', null, "content line 1\ncontent line 2 changed");
        $this->getRepository()->commit('commit2', true);
        $commit = $this->getRepository()->getCommit();

        $diff = Diff::create($this->getRepository(), $commit);

        $this->assertInstanceOf(\GitElephant\Objects\Diff\Diff::class, $diff);
        $this->assertArrayInterfaces($diff);
        $this->assertCount(1, $diff);
        $object = $diff[0];
        $this->assertInstanceOf(\GitElephant\Objects\Diff\DiffObject::class, $object);
        $this->assertArrayInterfaces($object);
        $this->assertCount(1, $object);
        $chunk = $object[0];
        $this->assertInstanceOf(\GitElephant\Objects\Diff\DiffChunk::class, $chunk);
        $this->assertArrayInterfaces($chunk);
        $this->assertCount(5, $chunk);

        $this->assertContainsOnlyInstancesOf(\GitElephant\Objects\Diff\DiffChunkLine::class, $chunk);

        $this->assertInstanceOf(\GitElephant\Objects\Diff\DiffChunkLineUnchanged::class, $chunk[0]);
        $this->assertInstanceOf(\GitElephant\Objects\Diff\DiffChunkLineChanged::class, $chunk[1]);
        $this->assertInstanceOf(\GitElephant\Objects\Diff\DiffChunkLineDeleted::class, $chunk[2]);
        $this->assertInstanceOf(\GitElephant\Objects\Diff\DiffChunkLineAdded::class, $chunk[3]);
        $this->assertInstanceOf(\GitElephant\Objects\Diff\DiffChunkLineUnchanged::class, $chunk[4]);
        
        $this->assertSame(1, $chunk[0]->getOriginNumber());
        $this->assertSame(1, $chunk[0]->getDestNumber());
        $this->assertSame(2, $chunk[1]->getNumber());
        $this->assertSame(2, $chunk[1]->getOriginNumber());
        $this->assertSame(3, $chunk[4]->getDestNumber());
        $this->assertSame(4, $chunk[4]->getOriginNumber());
    }

    private function assertArrayInterfaces($obj): void
    {
        $this->assertInstanceOf('\Iterator', $obj);
        $this->assertInstanceOf('\Countable', $obj);
        $this->assertInstanceOf('\ArrayAccess', $obj);
    }
}
