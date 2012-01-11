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
        $this->assertInstanceOf('\GitElephant\Objects\Commit\Message', $this->commit->getMessage());
        $this->assertEquals('first commit', $this->commit->getMessage()->toString());
        $this->assertRegExp('/^\w{40}$/', $this->commit->getSha());
        $this->assertEquals(array(), $this->commit->getParents());
        $this->addFile('foo2');
        $mainCommand = new MainCommand();
        $this->getCaller()->execute($mainCommand->add());
        $this->getCaller()->execute($mainCommand->commit('second commit'));
        $this->getCaller()->execute($showCommand->showCommit('HEAD'));
        $this->commit = new Commit($this->getCaller()->getOutputLines());
        $parents = $this->commit->getParents();
        $this->assertRegExp('/^\w{40}$/', $parents[0]);
    }

    /**
     * constructor regex test
     */
    public function testCommitRegEx()
    {
        $outputLines = array(
            "commit c277373174aa442af12a8e59de1812f3472c15f5",
            "tree 9d36a2d3c5d5bce9c6779a574ed2ba3d274d8016",
            "author matt <matteog@gmail.com> 1326214449 +0100",
            "committer matt <matteog@gmail.com> 1326214449 +0100",
            "",
            "    first commit"
        );

        $commit = new Commit($outputLines);
        $committer = $commit->getCommitter();
        $author = $commit->getAuthor();
        $this->assertEquals('matt', $committer->getName());
        $this->assertEquals('matt', $author->getName());

        $outputLines = array(
            "commit c277373174aa442af12a8e59de1812f3472c15f5",
            "tree 9d36a2d3c5d5bce9c6779a574ed2ba3d274d8016",
            "author matt jack <matteog@gmail.com> 1326214449 +0100",
            "committer matt jack <matteog@gmail.com> 1326214449 +0100",
            "",
            "    first commit"
        );
        $commit = new Commit($outputLines);
        $committer = $commit->getCommitter();
        $author = $commit->getAuthor();
        $this->assertEquals('matt jack', $committer->getName());
        $this->assertEquals('matt jack', $author->getName());
    }
}
