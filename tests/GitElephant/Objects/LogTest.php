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

namespace GitElephant\Objects;

use GitElephant\TestCase;
use GitElephant\Objects\Log;

/**
 * LogTest
 *
 * @author Mathias Geat <mathias@ailoo.net>
 */
class LogTest extends TestCase
{
    public function setUp()
    {
        $this->initRepository();
        $this->getRepository()->init();

        for ($i = 0; $i < 10; $i++) {
            $this->addFile('test file ' . $i);
            $this->getRepository()->commit('test commit index:' . $i, true);
        }
    }

    public function testLogCountable()
    {
        $log = $this->getRepository()->getLog();
        $this->assertEquals($log->count(), count($log));
    }

    public function testLogCountLimit()
    {
        $log = $this->getRepository()->getLog(null, null, null);
        $this->assertEquals(10, $log->count());

        $log = $this->getRepository()->getLog(null, null, 10, null);
        $this->assertEquals(10, $log->count());

        $log = $this->getRepository()->getLog(null, null, 50, null);
        $this->assertEquals(10, $log->count());

        $log = $this->getRepository()->getLog(null, null, 60, null);
        $this->assertEquals(10, $log->count());

        $log = $this->getRepository()->getLog(null, null, 1, null);
        $this->assertEquals(1, $log->count());

        $log = $this->getRepository()->getLog(null, null, 0, null);
        $this->assertEquals(0, $log->count());

        $log = $this->getRepository()->getLog(null, null, -1, null);
        $this->assertEquals(10, $log->count());

        $log = $this->getRepository()->getLog(null, "test\ file\ 1", -1, null);
        $this->assertEquals(1, $log->count());

        $log = $this->getRepository()->getLog(null, "test\ file*", -1, null);
        $this->assertEquals(10, $log->count());
    }

    public function testLogOffset()
    {
        $log = $this->getRepository()->getLog(null, null, null, 0);
        $this->assertEquals(10, $log->count());

        $log = $this->getRepository()->getLog(null, null, null, 5);
        $this->assertEquals(5, $log->count());

        $log = $this->getRepository()->getLog(null, null, null, 50);
        $this->assertEquals(0, $log->count());

        $log = $this->getRepository()->getLog(null, null, null, 100);
        $this->assertEquals(0, $log->count());
    }

    public function testLogIndex()
    {
        $log = $this->getRepository()->getLog(null, null, null, null);

        // [0;50[ - 10 = 39
        $this->assertEquals('test commit index:7', $log[2]->getMessage()->toString());
        $this->assertEquals('test commit index:7', $log->index(2)->getMessage()->toString());
        $this->assertEquals('test commit index:7', $log->offsetGet(2)->getMessage()->toString());
    }

    public function testLogToArray()
    {
        $log = $this->getRepository()->getLog(null, null, null, null);

        $this->assertTrue(is_array($log->toArray()));
        $this->assertInternalType('array', $log->toArray());
        $this->assertEquals($log->count(), count($log->toArray()));
    }

    public function testTreeObjectLog()
    {
        $tree = $this->getRepository()->getTree();
        $file = $tree[0];
    }

    public function testLogCreatedFromOutputLines()
    {
        $tree = $this->getRepository()->getTree();
        $obj = $tree[count($tree) - 1];
        $logCommand = new \GitElephant\Command\LogCommand();
        $command = $logCommand->showObjectLog($obj);
        $log = Log::createFromOutputLines($this->getRepository(), $this->caller->execute($command)->getOutputLines());
        $this->assertInstanceOf('GitElephant\Objects\Log', $log);
        $this->assertCount(0, $log);
        //$log = Log::createFromOutputLines($this->getRepository(), $this->caller->execute($command)->getOutputLines());

    }
}