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
        $this->initRepository();
        $this->repository->init();
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
    }
}