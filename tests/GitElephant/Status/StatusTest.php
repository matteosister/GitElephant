<?php
/**
 * User: matteo
 * Date: 28/05/13
 * Time: 21.42
 * Just for fun...
 */

namespace GitElephant\Status;

use GitElephant\TestCase;

/**
 * Class StatusTest
 *
 * @package GitElephant\Status
 */
class StatusTest extends TestCase
{
    /**
     * setUp
     */
    public function setUp()
    {
        $this->getRepository()->init();
    }

    /**
     * status test
     */
    public function testUntracked()
    {
        $this->markTestSkipped('working');
        $this->addFile('test');
        $s = $this->repository->getStatus();
        $this->assertCount(1, $s->untracked());
        $this->assertEquals('untracked', $s->untracked()->first()->getDescription());
    }

    /**
     * modified
     */
    public function testModified()
    {
        $this->markTestSkipped('working');
        $this->addFile('test', null, 'test');
        $this->repository->stage();
        $this->updateFile('test', 'test content');
        $s = $this->repository->getStatus();
        $this->assertCount(1, $s->modified());
    }

    /**
     * added
     */
    public function testAdded()
    {
        $this->markTestSkipped('working');
        $this->addFile('test');
        $this->repository->stage();
        $s = $this->repository->getStatus();
        $this->assertCount(1, $s->added());
    }

    /**
     * deleted
     */
    public function testDeleted()
    {
        $this->markTestSkipped('working');
        $this->addFile('test');
        $this->repository->commit('test message', true);
    }

    /**
     * renamed
     */
    public function testRenamed()
    {
        $this->markTestSkipped('working');
        $this->addFile('test');
    }

    /**
     * copied
     */
    public function testCopied()
    {
        $this->markTestSkipped('working');
        $this->addFile('test');
    }
}