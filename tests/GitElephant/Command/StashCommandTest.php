<?php

/**
 * User: matteo
 * Date: 06/06/13
 * Time: 23.45
 * Just for fun...
 */

namespace GitElephant\Command;

use GitElephant\TestCase;

/**
 * Class StashCommandTest
 *
 * @package GitElephant\Command
 */
class StashCommandTest extends TestCase
{
    /**
     * testSave
     */
    public function testSave(): void
    {
        $command = StashCommand::getInstance();
        $this->assertEquals("stash save 'Test'", $command->save('Test'));
        $this->assertEquals("stash save '--include-untracked' '--keep-index' 'Test'", $command->save('Test', true, true));
    }

    /**
     * testList
     */
    public function testList(): void
    {
        $command = StashCommand::getInstance();
        $this->assertEquals("stash list", $command->listStashes());
        $this->assertEquals("stash list '-p'", $command->listStashes(array('-p')));
    }

    /**
     * testShow
     */
    public function testShow(): void
    {
        $command = StashCommand::getInstance();
        $this->assertEquals("stash show 'stash@{0}'", $command->show(0));
    }

    /**
     * testDrop
     */
    public function testDrop(): void
    {
        $command = StashCommand::getInstance();
        $this->assertEquals("stash drop 'stash@{0}'", $command->drop(0));
    }

    /**
     * testApply
     */
    public function testApply(): void
    {
        $command = StashCommand::getInstance();
        $this->assertEquals("stash apply 'stash@{0}'", $command->apply(0));
        $this->assertEquals("stash apply '--index' 'stash@{0}'", $command->apply(0, true));
    }

    /**
     * testPop
     */
    public function testPop(): void
    {
        $command = StashCommand::getInstance();
        $this->assertEquals("stash pop 'stash@{0}'", $command->pop(0));
        $this->assertEquals("stash pop '--index' 'stash@{0}'", $command->pop(0, true));
    }

    /**
     * testBranch
     */
    public function testBranch(): void
    {
        $command = StashCommand::getInstance();
        $this->assertEquals("stash branch 'testbranch' 'stash@{0}'", $command->branch('testbranch', 0));
    }

    /**
     * testClear
     */
    public function testClear(): void
    {
        $command = StashCommand::getInstance();
        $this->assertEquals("stash clear", $command->clear());
    }

    /**
     * testCreate
     */
    public function testCreate(): void
    {
        $command = StashCommand::getInstance();
        $this->assertEquals("stash create", $command->create());
    }
}
