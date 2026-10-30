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

        $this->assertNotNull($diff->getRaw());
        $this->assertStringContainsString('content line 2 changed', $diff->getRaw());
        $this->assertSame($diff->getRaw(), (string) $diff);
    }

    public function testRawDiff(): void
    {
        $this->getRepository()->init(false, 'master');
        $this->addFile('foo', null, "content line 1\ncontent line 2\ncontent line 3");
        $this->getRepository()->commit('commit1', true);
        $this->addFile('foo', null, "content line 1\ncontent line 2 changed");
        $this->getRepository()->commit('commit2', true);
        $commit = $this->getRepository()->getCommit();

        $diff = Diff::create($this->getRepository(), $commit);

        $this->assertInstanceOf(\Stringable::class, $diff);
        $this->assertIsString($diff->getRaw());
        $this->assertStringContainsString('diff --git', $diff->getRaw());
        $this->assertStringContainsString('-content line 2', $diff->getRaw());
        $this->assertStringContainsString('+content line 2 changed', $diff->getRaw());
        $this->assertSame($diff->getRaw(), (string) $diff);

        $emptyDiff = new Diff($this->getRepository());
        $this->assertNull($emptyDiff->getRaw());
        $this->assertSame('', (string) $emptyDiff);

        $customDiff = new Diff($this->getRepository(), [], 'custom raw diff');
        $this->assertSame('custom raw diff', $customDiff->getRaw());
        $this->assertSame('custom raw diff', (string) $customDiff);
    }

    public function testDiffFileModeChangeWithContentChange(): void
    {
        $this->getRepository()->init(false, 'master');
        $this->addFile('script.sh', null, "#!/bin/sh\necho 'hello'\n");
        $this->getRepository()->commit('commit1', true);

        chmod($this->path . DIRECTORY_SEPARATOR . 'script.sh', 0755);
        $this->updateFile('script.sh', "#!/bin/sh\necho 'hello world'\n");
        $this->getRepository()->commit('commit2', true);
        $commit = $this->getRepository()->getCommit();

        $diff = Diff::create($this->getRepository(), $commit);

        $this->assertCount(1, $diff);
        $object = $diff[0];
        $this->assertInstanceOf(DiffObject::class, $object);
        $this->assertSame(DiffObject::MODE_MODE, $object->getMode());
        $this->assertCount(1, $object);
        $chunk = $object[0];
        $this->assertInstanceOf(DiffChunk::class, $chunk);
        $this->assertGreaterThan(0, count($chunk));
    }

    public function testDiffFileModeChangeWithoutContentChange(): void
    {
        $this->getRepository()->init(false, 'master');
        $this->addFile('script.sh', null, "#!/bin/sh\necho 'hello'\n");
        $this->getRepository()->commit('commit1', true);

        chmod($this->path . DIRECTORY_SEPARATOR . 'script.sh', 0755);
        $this->getRepository()->commit('commit2', true);
        $commit = $this->getRepository()->getCommit();

        $diff = Diff::create($this->getRepository(), $commit);

        $this->assertCount(1, $diff);
        $object = $diff[0];
        $this->assertInstanceOf(DiffObject::class, $object);
        $this->assertSame(DiffObject::MODE_MODE, $object->getMode());
        $this->assertCount(0, $object);
        $this->assertSame([], $object->getChunks());
    }

    public function testDiffObjectFileModeChangeWithContentChange(): void
    {
        $lines = [
            'diff --git SRC/.env.example DST/.env.example',
            'old mode 100644',
            'new mode 100755',
            'index 214b462..fbb124e 100755',
            '--- SRC/.env.example',
            '+++ DST/.env.example',
            '@@ -1,5 +1,6 @@',
            ' line 1',
            '+line 2',
        ];
        $object = new DiffObject($lines);
        $this->assertSame(DiffObject::MODE_MODE, $object->getMode());
        $this->assertCount(1, $object);
        $this->assertSame('.env.example', $object->getOriginalPath());
        $this->assertSame('.env.example', $object->getDestinationPath());
    }

    public function testDiffObjectFileModeChangeWithoutContentChange(): void
    {
        $lines = [
            'diff --git SRC/.env.example DST/.env.example',
            'old mode 100644',
            'new mode 100755',
        ];
        $object = new DiffObject($lines);
        $this->assertSame(DiffObject::MODE_MODE, $object->getMode());
        $this->assertCount(0, $object);
        $this->assertSame([], $object->getChunks());
    }

    public function testDiffObjectModeFallbackWhenUnmatched(): void
    {
        $lines = [
            'diff --git SRC/file DST/file',
            'unknown header',
        ];
        $object = new DiffObject($lines);
        $this->assertSame('', $object->getMode());
    }

    public function testDiffObjectNewModeOnly(): void
    {
        $lines = [
            'diff --git SRC/file DST/file',
            'new mode 100755',
        ];
        $object = new DiffObject($lines);
        $this->assertSame(DiffObject::MODE_MODE, $object->getMode());
    }

    private function assertArrayInterfaces($obj): void
    {
        $this->assertInstanceOf('\Iterator', $obj);
        $this->assertInstanceOf('\Countable', $obj);
        $this->assertInstanceOf('\ArrayAccess', $obj);
    }
}
