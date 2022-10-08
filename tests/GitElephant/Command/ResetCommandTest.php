<?php

/**
 * Created by PhpStorm.
 * User: christian
 * Date: 3/2/16
 * Time: 1:11 PM
 */

namespace GitElephant\Command;

use GitElephant\TestCase;

class ResetCommandTest extends TestCase
{
    /**
     * setUp
     */
    public function setUp(): void
    {
        $this->initRepository();
        $this->getRepository()->init(false, 'master');
        $this->addFile('test');
        $this->getRepository()->commit('test', true);
        $this->getRepository()->createBranch('test', 'master');
    }

    public function testResetHard(): void
    {
        $rstc = ResetCommand::getInstance();
        $this->assertEquals("reset '--hard' 'dbeac'", $rstc->reset('dbeac', [ResetCommand::OPTION_HARD]));
    }
}
