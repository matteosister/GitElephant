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
        $this->addFile('test');
        $s = $this->repository->getStatus();
        $this->assertCount(1, $s->untracked());
        $this->assertEquals('untracked', $s->untracked()->first()->getDescription());
        $this->assertFalse($s->untracked()->first()->isRenamed());
    }

    /**
     * modified
     */
    public function testModified()
    {
        $this->addFile('test', null, 'test');
        $this->repository->stage();
        $this->updateFile('test', 'test content');
        $s = $this->repository->getStatus();
        $this->assertCount(1, $s->modified());
        $this->assertFalse($s->modified()->first()->isRenamed());
    }

    /**
     * added
     */
    public function testAdded()
    {
        $this->addFile('test');
        $this->repository->stage();
        $s = $this->repository->getStatus();
        $this->assertCount(1, $s->added());
        $this->assertFalse($s->added()->first()->isRenamed());
    }

    /**
     * deleted
     */
    public function testDeleted()
    {
        $this->addFile('test');
        $this->repository->commit('test message', true);
        $this->removeFile('test');
        $s = $this->repository->getStatus();
        $this->assertCount(1, $s->deleted());
        $this->assertFalse($s->deleted()->first()->isRenamed());
    }

    /**
     * renamed
     */
    public function testRenamed()
    {
        $this->addFile('test', null, 'test content');
        $this->repository->commit('test message', true);
        $this->renameFile('test', 'test2');
        $s = $this->repository->getStatus();
        $this->assertCount(1, $s->renamed());
        $this->assertTrue($s->renamed()->first()->isRenamed());
    }
}