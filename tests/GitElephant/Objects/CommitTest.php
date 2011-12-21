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
use GitElephant\Objects\Commit;
use GitElephant\Command\ShowCommand;
use GitElephant\Command\MainCommand;


/**
 * CommitTest
 *
 * @author Matteo Giachino <matteog@gmail.com>
 */

class CommitTest extends TestCase
{
    /**
     * @var \GitElephant\Objects\Commit;
     */
    private $commit;

    public function setUp()
    {
        $this->initRepository();
        $mainCommand = new MainCommand();
        $this->getCaller()->execute($mainCommand->init());
        $this->addFile('foo');
        $this->getCaller()->execute($mainCommand->add());
        $this->getCaller()->execute($mainCommand->commit('first commit'));
    }

    /**
     * commit tests
     */
    public function testCommit()
    {
        $showCommand = new ShowCommand();
        $this->getCaller()->execute($showCommand->showCommit('HEAD'));
        $this->commit = new Commit($this->getCaller()->getOutputLines());

        $this->assertInstanceOf('\GitElephant\Objects\Commit', $this->commit);
        $this->assertInstanceOf('\GitElephant\Objects\GitAuthor', $this->commit->getAuthor());
        $this->assertInstanceOf('\GitElephant\Objects\GitAuthor', $this->commit->getCommitter());
        $this->assertInstanceOf('\Datetime', $this->commit->getDatetimeAuthor());
        $this->assertInstanceOf('\Datetime', $this->commit->getDatetimeCommitter());
        $this->assertEquals(array('first commit'), $this->commit->getMessage());
        $this->assertRegExp('/^\w{40}$/', $this->commit->getSha());
        $this->assertEquals('', $this->commit->getParent());
        $this->addFile('foo2');
        $mainCommand = new MainCommand();
        $this->getCaller()->execute($mainCommand->add());
        $this->getCaller()->execute($mainCommand->commit('second commit'));
        $this->getCaller()->execute($showCommand->showCommit('HEAD'));
        $this->commit = new Commit($this->getCaller()->getOutputLines());
        $this->assertRegExp('/^\w{40}$/', $this->commit->getParent());
    }
}
